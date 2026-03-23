<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Holiday\Exception\UnsupportedYearException;
use App\Domain\Leave\Exception\UnsupportedRecommendationStrategyException;
use App\Domain\Leave\LeaveRecommendation;
use App\Domain\Leave\LeaveRecommendationEngineInterface;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class LeaveRecommendationsController
{
    private const int MAX_RANGE_DAYS = 366;

    public function __construct(private LeaveRecommendationEngineInterface $recommendationEngine)
    {
    }

    #[Route('/api/v1/leave/recommendations', name: 'api_v1_leave_recommendations', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $fromInput = $request->query->get('from');
        if (!\is_string($fromInput) || $fromInput === '') {
            return $this->badRequest('missing_from', 'Query parameter "from" is required.');
        }

        $toInput = $request->query->get('to');
        if (!\is_string($toInput) || $toInput === '') {
            return $this->badRequest('missing_to', 'Query parameter "to" is required.');
        }

        $budgetInput = $request->query->get('budget');
        if (!\is_string($budgetInput) || $budgetInput === '') {
            return $this->badRequest('missing_budget', 'Query parameter "budget" is required.');
        }

        $budget = filter_var($budgetInput, FILTER_VALIDATE_INT);
        if ($budget === false || $budget < 0) {
            return $this->badRequest('invalid_budget', 'Query parameter "budget" must be an integer greater or equal to 0.');
        }

        $strategyInput = $request->query->get('strategy', LeaveRecommendationEngineInterface::STRATEGY_BEST_RATIO);
        if ($strategyInput === '') {
            return $this->badRequest('invalid_strategy', 'Query parameter "strategy" must be a non-empty string.');
        }

        $from = $this->parseIsoDate($fromInput);
        if (!$from instanceof DateTimeImmutable) {
            return $this->badRequest('invalid_from', 'Query parameter "from" must use format YYYY-MM-DD.');
        }

        $to = $this->parseIsoDate($toInput);
        if (!$to instanceof DateTimeImmutable) {
            return $this->badRequest('invalid_to', 'Query parameter "to" must use format YYYY-MM-DD.');
        }

        if ($from > $to) {
            return $this->badRequest('invalid_range', 'Query parameter "from" must be before or equal to "to".');
        }

        $rangeDays = (int) $from->diff($to)->days + 1;
        if ($rangeDays > self::MAX_RANGE_DAYS) {
            return $this->badRequest(
                'range_too_large',
                sprintf('Date range cannot exceed %d days.', self::MAX_RANGE_DAYS)
            );
        }

        try {
            $recommendations = $this->recommendationEngine->recommend($from, $to, $budget, $strategyInput);
        } catch (UnsupportedYearException $exception) {
            return $this->badRequest('unsupported_year', $exception->getMessage());
        } catch (UnsupportedRecommendationStrategyException $exception) {
            return $this->badRequest('invalid_strategy', $exception->getMessage());
        }

        return new JsonResponse(
            [
                'country' => 'PL',
                'strategy' => $strategyInput,
                'budget' => $budget,
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'recommendations' => array_map(
                    static fn (LeaveRecommendation $recommendation): array => $recommendation->toArray(),
                    $recommendations
                ),
            ],
            Response::HTTP_OK
        );
    }

    private function parseIsoDate(string $value): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date instanceof DateTimeImmutable) {
            return null;
        }

        if ($date->format('Y-m-d') !== $value) {
            return null;
        }

        return $date;
    }

    private function badRequest(string $code, string $message): JsonResponse
    {
        return new JsonResponse(
            [
                'error' => [
                    'code' => $code,
                    'message' => $message,
                ],
            ],
            Response::HTTP_BAD_REQUEST
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Holiday\Exception\UnsupportedYearException;
use App\Domain\Workday\WorkdayRangeCalculatorInterface;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class WorkdaysRangeController
{
    private const MAX_RANGE_DAYS = 366;

    public function __construct(private WorkdayRangeCalculatorInterface $workdayRangeCalculator)
    {
    }

    #[Route('/api/v1/workdays/range', name: 'api_v1_workdays_range', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $fromInput = $request->query->get('from');
        if (!is_string($fromInput) || $fromInput === '') {
            return $this->badRequest('missing_from', 'Query parameter "from" is required.');
        }

        $toInput = $request->query->get('to');
        if (!is_string($toInput) || $toInput === '') {
            return $this->badRequest('missing_to', 'Query parameter "to" is required.');
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
            $summary = $this->workdayRangeCalculator->calculate($from, $to);
        } catch (UnsupportedYearException $exception) {
            return $this->badRequest('unsupported_year', $exception->getMessage());
        }

        $payload = $summary->toArray();
        $payload['country'] = 'PL';

        return new JsonResponse($payload, Response::HTTP_OK);
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

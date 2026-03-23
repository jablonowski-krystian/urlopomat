<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Holiday\Exception\UnsupportedYearException;
use App\Domain\Holiday\Holiday;
use App\Domain\Holiday\HolidayProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class HolidaysController
{
    public function __construct(private HolidayProviderInterface $holidayProvider)
    {
    }

    #[Route('/api/v1/holidays', name: 'api_v1_holidays', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $yearValue = $request->query->get('year');

        if (!is_string($yearValue) || $yearValue === '') {
            return $this->badRequest(
                'missing_year',
                'Query parameter "year" is required and must be a non-empty string.'
            );
        }

        $year = filter_var($yearValue, FILTER_VALIDATE_INT);
        if ($year === false) {
            return $this->badRequest('invalid_year', 'Query parameter "year" must be a valid integer.');
        }

        try {
            $holidays = $this->holidayProvider->getForYear($year);
        } catch (UnsupportedYearException $exception) {
            return $this->badRequest('unsupported_year', $exception->getMessage());
        }

        return new JsonResponse(
            [
                'country' => 'PL',
                'year' => $year,
                'holidays' => array_map(
                    static fn(Holiday $holiday): array => $holiday->toArray(),
                    $holidays
                ),
            ],
            Response::HTTP_OK
        );
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

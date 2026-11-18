<?php

namespace App\Http\Controllers\Api;

use App\Enums\QuoteStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveQuoteRequest;
use App\Http\Requests\UpdateQuoteStatusRequest;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use App\Services\QuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Thin controller: validation in FormRequests, rules in QuoteService,
 * JSON shape in QuoteResource.
 */
class QuoteController extends Controller
{
    public function __construct(private QuoteService $quotes) {}

    /**
     * Paginated list, newest first. Filters: ?status= and ?customer_id=.
     * Lines are eager loaded because the list shows each quote's total.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $quotes = Quote::query()
            ->with(['customer', 'lines'])
            ->when($request->enum('status', QuoteStatus::class), fn ($query, QuoteStatus $status) => $query->where('status', $status))
            ->when($request->integer('customer_id'), fn ($query, int $customerId) => $query->where('customer_id', $customerId))
            ->orderByDesc('number')
            ->paginate(15)
            ->withQueryString();

        return QuoteResource::collection($quotes);
    }

    public function store(SaveQuoteRequest $request): JsonResponse
    {
        $quote = $this->quotes->create($request->validated());

        return (new QuoteResource($quote->load(['customer', 'lines'])))->response()->setStatusCode(201);
    }

    public function show(Quote $quote): QuoteResource
    {
        return new QuoteResource($quote->load(['customer', 'lines']));
    }

    public function update(SaveQuoteRequest $request, Quote $quote): QuoteResource
    {
        $this->quotes->update($quote, $request->validated());

        return new QuoteResource($quote->load(['customer', 'lines']));
    }

    public function destroy(Quote $quote): Response
    {
        $this->quotes->delete($quote);

        return response()->noContent();
    }

    public function updateStatus(UpdateQuoteStatusRequest $request, Quote $quote): QuoteResource
    {
        $this->quotes->changeStatus($quote, $request->enum('status', QuoteStatus::class));

        return new QuoteResource($quote->load(['customer', 'lines']));
    }
}

<?php

namespace App\Http\Controllers;

use App\Model\Category;
use App\Model\Import\TransactionFileParserFactory;
use App\Model\Statistics\AccountStatsGenerator;
use App\Model\Transaction;
use App\Resources\Transaction as TransactionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    use SortAndPaginate;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $queryBuilder = Transaction::with(['category', 'account'])
            ->leftJoin('categories as category', 'transactions.category_id', '=', 'category.id')
            ->leftJoin('accounts as account', 'transactions.account_id', '=', 'account.id');

        return TransactionResource::collection($this->getPaginatedAndSorted($request, $queryBuilder, ['transactions.*']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $transaction = Transaction::create($request->all());

        return new TransactionResource($transaction);
    }

    public function import(Request $request)
    {
        $type = $request->get("type");
        $file = $request->file("file");

        $importer = TransactionFileParserFactory::build($type);

        if(!$importer) {
            Log::warning("No importer found for type " . $type);
            return response()->json(null, 400);
        }

        $transactions = $importer->parse($file);

        if(!$request->get("dryRun")) {
            // Start importing
            Transaction::insert($transactions);
            Log::info("Imported " . count($transactions) . " into the database");
        }

        return response()->json(["transactionCount" => count($transactions)], 201);
    }

    /**
     * Recompute summary statistics
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function stats()
    {
        $generator = new AccountStatsGenerator();
        $stats = $generator->run();

        return response()->json(["numStats" => count($stats)], 204);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        return new TransactionResource($transaction);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        $transaction->update($request->all());
        return new TransactionResource($transaction);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(null, 204);
    }
}

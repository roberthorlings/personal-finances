<?php

namespace App\Http\Controllers;

use App\Model\Category;
use App\Model\Import\TransactionFileParserFactory;
use App\Model\Statistics\AccountStatsGenerator;
use App\Model\Statistics\CategoryStatsGenerator;
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

        if($request->has("category_id")) {
            $category_id = $request->get("category_id");

            if($category_id == 0) {
                // If 0 is given as category id, it means a request for all uncategorized transactions
                $queryBuilder->whereNull("category_id");
            } else {
                // Otherwise, we need to return the transactions within the category or any of its descendants
                $categories = Category::descendantsOf($category_id)->pluck('id');

                // Include the id of category itself
                $categories[] = $category_id;

                // Filter on those categories
                $queryBuilder->whereIn("category_id", $categories);
            }
        }

        if($request->has("account_id")) {
            $account_id = $request->get("account_id");
            $queryBuilder->where("account_id", "=", $account_id);
        }

        if($request->has("date_start")) {
            $queryBuilder->where("date", ">=", $request->get("date_start"));
        }

        if($request->has("date_end")) {
            $queryBuilder->where("date", "<=", $request->get("date_end"));
        }



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
        $accountStats = $generator->run();

        $generator = new CategoryStatsGenerator();
        $categoryStats = $generator->run();

        return response()->json([
            "numAccountStats" => count($accountStats),
            "numCategoryStats" => count($categoryStats)
        ], 201);
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

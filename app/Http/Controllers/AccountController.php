<?php

namespace App\Http\Controllers;

use App\Model\Statistics\AccountStatsGenerator;
use Illuminate\Http\Request;
use App\Model\Account;
use App\Resources\Account as AccountResource;

class AccountController extends Controller
{
    use SortAndPaginate;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return AccountResource::collection(
            $this->getPaginatedAndSorted($request, Account::query())
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $account = Account::create($request->all());

        return new AccountResource($account);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        return new AccountResource($account);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Account $account)
    {
        $account->update($request->all());
        return new AccountResource($account);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        $account->delete();
        return response()->json(null, 204);
    }

    /**
     * Remove the specified resource from storage.
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
}

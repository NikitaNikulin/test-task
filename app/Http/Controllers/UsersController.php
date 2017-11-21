<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function getBalance(Request $request)
    {
	    $this->validate($request, [
		    'user' => 'required|int|exists:users,id',
	    ]);
	    $userId = $request->get('user');

    	$user = User::whereId($userId)->first();

        return ["balance" => $user->balance];
    }

	public function deposit(Request $request)
	{
		$request_body = file_get_contents('php://input');
		$data = json_decode($request_body, True);
		if($data)
			$request->merge($data);
		$this->validate($request, [
			'user' => 'required|int|exists:users,id',
			'amount' => 'required|int|min:1',
		]);
		$userId = array_get($data, 'user');
		$amount = array_get($data, 'amount');
		$user = User::whereId($userId)->first();

		if($user)
			$user->update(['balance' => $user->balance + $amount]);
		else
			$user = User::create(['id' => $userId, 'balance' => $amount]);

		return;
	}

	public function withdraw(Request $request)
	{
		$request_body = file_get_contents('php://input');
		$data = json_decode($request_body, True);
		if($data)
			$request->merge($data);
		$this->validate($request, [
			'user' => 'required|int|exists:users,id',
			'amount' => 'required|int|min:1',
		]);
		$userId = array_get($data, 'user');
		$amount = array_get($data, 'amount');
		$user = User::whereId($userId)->first();

		if($user->balance >= $amount)
			$user->update(['balance' => $user->balance - $amount]);
		else
			return $this->lowBalanceException();

		return;
	}

	public function transfer(Request $request)
	{
		$request_body = file_get_contents('php://input');
		$data = json_decode($request_body, True);
		if($data)
			$request->merge($data);
		$this->validate($request, [
			'from' => 'required|int|exists:users,id',
			'amount' => 'required|int|min:1',
			'to' => 'required|int|exists:users,id',
		]);
		$fromUserId = array_get($data, 'from');
		$toUserId = array_get($data, 'to');
		$amount = array_get($data, 'amount');
		$fromUser = User::whereId($fromUserId)->first();
		$toUser = User::whereId($toUserId)->first();

		if($fromUser->balance >= $amount) {
			$fromUser->update(['balance' => $fromUser->balance - $amount]);
			$toUser->update(['balance' => $toUser->balance + $amount]);
		} else
			return $this->lowBalanceException();

		return;
	}

	protected function lowBalanceException()
	{
		return new JsonResponse(["balance" => ["Not enough money on user balance"]], 422);
	}
}

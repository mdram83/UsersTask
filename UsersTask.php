<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UsersTask extends Controller
{
    public function updateUsers()
    {
        $users = $this->validateUsers(['users.*.id' => 'required|exists:users']);

        DB::beginTransaction();

        if (count($errors = $this->updateAndReturnErrors($users)) > 0) {
            DB::rollBack();
            return redirect()->back()->withErrors($errors)->withInput();
        }

        DB::commit();

        return redirect()->back()->with('success', 'All users updated.');
    }

    public function storeUsers()
    {
        $users = $this->validateUsers();

        DB::beginTransaction();

        if (count($errors = $this->insertAndReturnErrors($users)) > 0) {
            DB::rollBack();
            return redirect()->back()->withErrors($errors)->withInput();
        }

        DB::commit();

        $this->sendEmail($users);

        return redirect()->back()->with('success', 'All users created.');
    }

    protected function sendEmail(array $users) : void
    {
        foreach ($users as $user) {
            Mail::to($user['email'])
                ->cc('support@company.com')
                ->queue(new WelcomeMail($user['login']));
        }
    }

    protected function validateUsers(array $rules = [])
    {
        return request()->validate(array_merge([
            'users.*.name' => 'required|min:10',
            'users.*.login' => 'required',
            'users.*.email' => 'required|email',
            'users.*.password' => 'required',
        ], $rules))['users'];
    }

    protected function insertAndReturnErrors(array $users) : array
    {
        $errors = [];

        foreach ($users as $user) {
            try {
                DB::table('users')->insert($this->prepareParams($user));
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        return $errors;
    }

    protected function updateAndReturnErrors(array $users) : array
    {
        $errors = [];

        foreach ($users as $user) {
            try {
                DB::table('users')->where('id', $user['id'])->update($this->prepareParams($user));
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        return $errors;
    }

    protected function prepareParams(array $user) : array
    {
        return [
            'name' => $user['name'],
            'login' => $user['login'],
            'email' => $user['email'],
            'password' => bcrypt($user['password']),
        ];
    }
}

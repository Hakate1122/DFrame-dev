<?php

namespace App\Controller;

use App\Model\Users;
use DFrame\Application\Validator;

class UserController extends Controller
{
    public function __construct(private Users $users)
    {
    }
    public function listUsers()
    {
        $allUsers = $this->users->all();
        return $this->render('user1/list', ['users' => $allUsers]);
    }

    public function addUser()
    {
        return $this->render('user1/add');
    }

    public function storeUser(Validator $validator)
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';

        $validator->make(
            [
                'name' => $name,
                'email' => $email
            ],
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255'
            ],
            [
                'name.required' => 'Name is required.',
                'name.string' => 'Name must be a string.',
                'name.max' => 'Name must not exceed 255 characters.',
                'email.required' => 'Email is required.',
                'email.email' => 'Email must be a valid email address.',
                'email.max' => 'Email must not exceed 255 characters.'
            ]
        );

        if ($validator->fails()) {
            return $this->render('user1/add', ['error' => $validator->first()]);
        }

        $this->users
            ->insert([
                'name' => $name,
                'email' => $email
            ])
            ->execute();
        return redirect()->route('user.list');
    }

    public function editUser($id)
    {
        return $this->render('user1/edit', ['user' => $this->users->where('id', $id)->first()]);
    }

    public function updateUser(Validator $validator, $id)
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';

        $user = $this->users->where('id', $id)->first();

        $validator->make(
            [
                'name' => $name,
                'email' => $email
            ],
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255'
            ],
            [
                'name.required' => 'Name is required.',
                'name.string' => 'Name must be a string.',
                'name.max' => 'Name must not exceed 255 characters.',
                'email.required' => 'Email is required.',
                'email.email' => 'Email must be a valid email address.',
                'email.max' => 'Email must not exceed 255 characters.'
            ]
        );

        if ($validator->fails()) {
            return $this->render('user1/edit', ['error' => $validator->errors(), 'user' => $user]);
        }

        $this->users
            ->where('id', $id)
            ->update([
                'name' => $name,
                'email' => $email
            ])
            ->execute();
        return redirect()->route('user.list');
    }
    public function deleteUser($id)
    {
        $this->users
            ->where('id', $id)
            ->delete()
            ->execute();
        return redirect()->route('user.list');
    }
}

<?php

namespace App\Livewire\Forms;

use App\Models\User;
use jeremykenedy\LaravelRoles\Models\Role;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{
    public ?User $user;

    public string $name;
    public string $email;
    public string $password;
    public ?string $password_confirmation = null;
    public  $role;

    public function rules()
    {
        $emailRule = 'required|email|max:255|unique:users,email';
        if (!empty($this->user) && $this->user && $this->user->id) {
            $emailRule = 'required|email|max:255|unique:users,email,' . $this->user->id;
        }

        if (!empty($this->user) && $this->user && $this->user->id) {
            $passwordRule = 'nullable|string|min:8|confirmed';
        } else {
            $passwordRule = 'required|string|min:8|confirmed';
        }

        return [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'password' => $passwordRule,
            'role' => 'required|exists:roles,id',
        ];
    }
 
    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles()->first()->id ?? 0; // Assuming role is a string, adjust if it's an enum or something else
    }

    public function save(): void
    {
        $this->validate();

        $role = Role::find($this->role);
        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if (!empty($this->password)) {
            $data['password'] = bcrypt($this->password);
        }

        $this->user->update($data);
        $this->user->syncRoles([$role]);

        session()->flash('message', 'User saved successfully.');
    }

    public function store(): void
    {
        $this->validate();

        $role = Role::find($this->role);
        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        $user = User::create($data);
        $user->attachRole($role);

        session()->flash('message', 'User created successfully.');
    }
}

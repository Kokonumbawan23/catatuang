<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class WalletController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('spa');
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('spa');
    }

    public function store(): RedirectResponse
    {
        return redirect()->route('spa');
    }

    public function show(): RedirectResponse
    {
        return redirect()->route('spa');
    }

    public function edit(): RedirectResponse
    {
        return redirect()->route('spa');
    }

    public function update(): RedirectResponse
    {
        return redirect()->route('spa');
    }

    public function destroy(): RedirectResponse
    {
        return redirect()->route('spa');
    }
}

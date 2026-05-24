<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard1');

Route::get('/servers', function () {
    return view('servers.index');
})->name('servers');

Route::get('/servers/{id}', function ($id) {
    return view('servers.show', ['serverId' => $id]);
})->name('servers.show');

Route::get('/alerts', function () {
    return view('alerts.index');
})->name('alerts');

Route::get('/anomalies', function () {
    return view('anomalies.index');
})->name('anomalies');

Route::get('/incidents', function () {
    return view('incidents.index');
})->name('incidents');

Route::get('/logs', function () {
    return view('logs.index');
})->name('logs');

Route::get('/settings', function () {
    return view('settings');
})->name('settings');

Route::get('/admin/audit-logs', function () {
    return view('admin.audit-logs');
})->name('admin.audit-logs');

Route::get('/admin/users', function () {
    return view('admin.users');
})->name('admin.users');
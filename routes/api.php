<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('webhook', [WebhookController::class, 'handleWebhook']);
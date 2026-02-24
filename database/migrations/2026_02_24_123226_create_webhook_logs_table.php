<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebhookLogsTable extends Migration
{
    public function up()
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->string('shop_domain');
            $table->json('payload');
            $table->timestamp('received_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('webhook_logs');
    }
}
<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Your API Documentation",
    description: "Shopify Order APIs"
)]
#[OA\Server(
    url: "/",
    description: "Local Server"
)]
#[OA\Server(
    url: "https://baee374c1509.ngrok-free.app/",
    description: "Production Server"
)]
class AppInfo
{
    // This class is only for Swagger metadata
}

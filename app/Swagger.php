<?php
/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Hotel Management System API",
 *     description="RESTful API for Hotel Management System with PMS integration",
 *     @OA\Contact(
 *         email="admin@hotel.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local Development Server"
 * )
 * 
 * @OA\Server(
 *     url="https://api.hotel.com",
 *     description="Production Server"
 * )
 * 
 * @OA\Tag(
 *     name="Bookings",
 *     description="Booking management endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Guests",
 *     description="Guest management endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Rooms",
 *     description="Room management endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="RoomTypes",
 *     description="Room type management endpoints"
 * )
 */ 
<?php

namespace App\Message;

class AppointmentCreated
{
    public function __construct(
        private int $appointmentId
    ) {}

    public function getAppointmentId(): int
    {
        return $this->appointmentId;
    }
}
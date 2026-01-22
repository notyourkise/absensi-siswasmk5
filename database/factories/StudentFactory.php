<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            // NISN acak 10 digit
            'nisn' => fake()->unique()->numerify('00########'), 
            // Nama acak orang Indonesia
            'nama' => fake()->name(),
            // Kelas acak
            'kelas' => fake()->randomElement(['X RPL 1', 'X RPL 2', 'XI TKJ 1', 'XII MM 2']),
            // Gender acak
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
        ];
    }
}
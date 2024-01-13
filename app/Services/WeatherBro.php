<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WeatherBro
{
    private ?array $forecast = null;

    private ?array $parsedForecast = null;

    private ?string $error = null;

    public function fetchForecast(string $forecastUrl): self
    {
        try {
            $this->forecast = Http::get($forecastUrl)->json();
        } catch (\Exception $e) {
            $this->error = "Nie rozpoznałem miejsca, o którym mówisz. Spróbuj jeszcze raz.";
        }

        return $this;
    }

    public function parseForecastData(): self
    {
        $this->parsedForecast = [
            "location" => [
                "name" => $this->forecast["location"]["name"] ?? null,
                "country" => $this->forecast["location"]["country"] ?? null,
                "localtime" => $this->forecast["location"]["localtime"] ?? null,
            ],
            "current" => [
                "temp_c" => $this->forecast["current"]["temp_c"] ?? null,
                "condition" => [
                    "text" => $this->forecast["current"]["condition"]["text"] ?? null,
                ],
                "wind_kph" => $this->forecast["current"]["wind_kph"] ?? null,
                "wind_degree" => $this->forecast["current"]["wind_degree"] ?? null,
                "wind_dir" => $this->forecast["current"]["wind_dir"] ?? null,
                "pressure_mb" => $this->forecast["current"]["pressure_mb"] ?? null,
                "precip_mm" => $this->forecast["current"]["precip_mm"] ?? null,
                "humidity" => $this->forecast["current"]["humidity"] ?? null,
                "cloud" => $this->forecast["current"]["cloud"] ?? null,
                "feelslike_c" => $this->forecast["current"]["feelslike_c"] ?? null,
            ],
            "forecast" => array_map(function ($forecastDay) {
                return [
                    "date" => $forecastDay["date"] ?? null,
                    "day" => [
                        "maxtemp_c" => $forecastDay["day"]["maxtemp_c"] ?? null,
                        "mintemp_c" => $forecastDay["day"]["mintemp_c"] ?? null,
                        "avgtemp_c" => $forecastDay["day"]["avgtemp_c"] ?? null,
                        "maxwind_kph" => $forecastDay["day"]["maxwind_kph"] ?? null,
                        "totalprecip_mm" => $forecastDay["day"]["totalprecip_mm"] ?? null,
                        "totalsnow_cm" => $forecastDay["day"]["totalsnow_cm"] ?? null,
                        "avgvis_km" => $forecastDay["day"]["avgvis_km"] ?? null,
                        "avghumidity" => $forecastDay["day"]["avghumidity"] ?? null,
                        "daily_will_it_rain" => $forecastDay["day"]["daily_will_it_rain"] ?? null,
                        "daily_chance_of_rain" => $forecastDay["day"]["daily_chance_of_rain"] ?? null,
                        "daily_will_it_snow" => $forecastDay["day"]["daily_will_it_snow"] ?? null,
                        "daily_chance_of_snow" => $forecastDay["day"]["daily_chance_of_snow"] ?? null,
                        "condition" => [
                            "text" => $forecastDay["day"]["condition"]["text"] ?? null,
                        ]
                    ],
                    "astro" => [
                        "sunrise" => $forecastDay["astro"]["sunrise"] ?? null,
                        "sunset" => $forecastDay["astro"]["sunset"] ?? null,
                        "moonrise" => $forecastDay["astro"]["moonrise"] ?? null,
                        "moonset" => $forecastDay["astro"]["moonset"] ?? null,
                        "moon_phase" => $forecastDay["astro"]["moon_phase"] ?? null,
                        "moon_illumination" => $forecastDay["astro"]["moon_illumination"] ?? null,
                        "is_moon_up" => $forecastDay["astro"]["is_moon_up"] ?? null,
                        "is_sun_up" => $forecastDay["astro"]["is_sun_up"] ?? null,
                    ]
                ];
            }, $this->forecast["forecast"]["forecastday"] ?? [])
        ];

        return $this;
    }

    public function toJson(): string
    {
        try {
            $encodedForecast = json_encode($this->parsedForecast);
        } catch (\Exception $e) {
            $this->error = "Mam problem z pobraniem aktualnych danych pogodowych. Spróbuj jeszcze raz.";
        }

        return $encodedForecast;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}
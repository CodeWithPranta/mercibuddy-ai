<?php

namespace Database\Seeders;

use App\Models\Artisan;
use App\Models\Category;
use App\Models\City;
use App\Models\Comment;
use App\Models\ContactDetail;
use App\Models\Country;
use App\Models\FilterValue;
use App\Models\Page;
use App\Models\Service;
use App\Models\Setting;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categoryNames = [
            'Beauty and Wellness',
            'Childcare',
            'Cleaning',
            'Elderly and Disability Care',
            'Fitness and Yoga',
            'Plumbing and Handyman',
            'Tutoring and Education',
            'Cooking and Catering',
            'Electrical Services',
            'Driving and Transport',
        ];

        $categories = collect($categoryNames)->mapWithKeys(function (string $name): array {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-6-6h12"/></svg>',
                ],
            );

            return [$name => $category];
        });

        $places = collect([
            ['shortname' => 'BD', 'name' => 'Bangladesh', 'phonecode' => '+880', 'state' => 'Dhaka', 'city' => 'Dhaka'],
            ['shortname' => 'IN', 'name' => 'India', 'phonecode' => '+91', 'state' => 'West Bengal', 'city' => 'Kolkata'],
            ['shortname' => 'GB', 'name' => 'United Kingdom', 'phonecode' => '+44', 'state' => 'England', 'city' => 'London'],
            ['shortname' => 'US', 'name' => 'United States', 'phonecode' => '+1', 'state' => 'New York', 'city' => 'New York City'],
            ['shortname' => 'CA', 'name' => 'Canada', 'phonecode' => '+1', 'state' => 'Ontario', 'city' => 'Toronto'],
            ['shortname' => 'AU', 'name' => 'Australia', 'phonecode' => '+61', 'state' => 'New South Wales', 'city' => 'Sydney'],
            ['shortname' => 'CH', 'name' => 'Switzerland', 'phonecode' => '+41', 'state' => 'Basel-Stadt', 'city' => 'Basel'],
            ['shortname' => 'FR', 'name' => 'France', 'phonecode' => '+33', 'state' => 'Brittany', 'city' => 'Rennes'],
        ])->mapWithKeys(function (array $countryData): array {
            $country = Country::updateOrCreate(
                ['name' => $countryData['name']],
                [
                    'shortname' => $countryData['shortname'],
                    'slug' => Str::slug($countryData['name']),
                    'phonecode' => $countryData['phonecode'],
                ],
            );

            $state = State::updateOrCreate([
                'name' => $countryData['state'],
                'country_id' => $country->id,
            ]);

            $city = City::updateOrCreate([
                'name' => $countryData['city'],
                'state_id' => $state->id,
            ]);

            return [$countryData['name'] => ['country' => $country, 'state' => $state, 'city' => $city]];
        })->all();

        $khulnaState = State::updateOrCreate([
            'name' => 'Khulna',
            'country_id' => $places['Bangladesh']['country']->id,
        ]);

        $khulnaCity = City::updateOrCreate([
            'name' => 'Khulna',
            'state_id' => $khulnaState->id,
        ]);

        $places['Bangladesh']['states'] = [
            'Dhaka' => ['state' => $places['Bangladesh']['state'], 'city' => $places['Bangladesh']['city']],
            'Khulna' => ['state' => $khulnaState, 'city' => $khulnaCity],
        ];

        User::firstOrCreate(
            ['email' => 'admin@mercibuddy.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'user_type' => 1,
                'email_verified_at' => now(),
            ],
        );

        $artisanSeeds = [
            [
                'name' => 'Rakib Ahmed', 'email' => 'rakib@mercibuddy.com',
                'category' => 'Beauty and Wellness', 'profession' => 'Beauty and Wellness', 'profession_type' => 'Full-time',
                'experience' => '5', 'country' => 'Bangladesh', 'education' => 'BSc in Computer Science',
                'address' => '123 Sample Street, Dhaka',
                'biography' => '<p>Experienced Beauty and Wellness with a passion for delivering quality work and helping clients achieve their goals.</p>',
            ],
            [
                'name' => 'Nusrat Jahan', 'email' => 'nusrat@mercibuddy.com',
                'category' => 'Childcare', 'profession' => 'Childcare', 'profession_type' => 'Part-time',
                'experience' => '7', 'country' => 'India', 'education' => 'BSc in Computer Science',
                'address' => '123 Sample Street, Kolkata',
                'biography' => '<p>Experienced Childcare with a passion for delivering quality work and helping clients achieve their goals.</p>',
            ],
            [
                'name' => 'Tanvir Both', 'email' => 'both@mercibuddy.com',
                'category' => 'Cleaning', 'profession' => 'Cleaning', 'profession_type' => 'Contract',
                'experience' => '4', 'country' => 'Bangladesh', 'education' => 'BSc in Computer Science',
                'address' => '123 Sample Street, Dhaka',
                'biography' => '<p>Experienced Cleaning with a passion for delivering quality work and helping clients achieve their goals.</p>',
            ],
            [
                'name' => 'Luca Meyer', 'email' => 'luca.meyer@mercibuddy.com',
                'category' => 'Fitness and Yoga', 'profession' => 'Fitness Instructor', 'profession_type' => 'Full-time',
                'experience' => '8', 'country' => 'Switzerland', 'education' => 'Diploma in Sports Science',
                'address' => '12 Clarastrasse, Basel',
                'biography' => '<p>Certified fitness instructor in Basel offering personal training, gym coaching and yoga sessions across Basel-Stadt, Switzerland.</p>',
            ],
            [
                'name' => 'Sophie Martin', 'email' => 'sophie.martin@mercibuddy.com',
                'category' => 'Plumbing and Handyman', 'profession' => 'Plumber', 'profession_type' => 'Full-time',
                'experience' => '10', 'country' => 'France', 'education' => 'Vocational Diploma in Plumbing',
                'address' => '8 Rue de Rennes, Rennes',
                'biography' => '<p>Rennes plumber and handyman. I fix leaking taps, repair leaks, unblock sinks and install taps quickly across Rennes, Brittany.</p>',
            ],
            [
                'name' => 'Anna Keller', 'email' => 'anna.keller@mercibuddy.com',
                'category' => 'Tutoring and Education', 'profession' => 'Science Tutor', 'profession_type' => 'Part-time',
                'experience' => '6', 'country' => 'Switzerland', 'education' => 'MSc in Physics',
                'address' => '45 Freiestrasse, Basel',
                'biography' => '<p>Science tutor in Basel for school and university students. Tutoring in physics, chemistry, biology and mathematics.</p>',
            ],
            [
                'name' => 'Julien Bernard', 'email' => 'julien.bernard@mercibuddy.com',
                'category' => 'Tutoring and Education', 'profession' => 'Science Tutor', 'profession_type' => 'Part-time',
                'experience' => '5', 'country' => 'France', 'education' => 'MSc in Chemistry',
                'address' => '21 Place des Lices, Rennes',
                'biography' => '<p>Science tutor in Rennes helping students with physics, chemistry and biology tutoring, from homework to exam preparation.</p>',
            ],
            [
                'name' => 'Arif Hossain', 'email' => 'arif.hossain@mercibuddy.com',
                'category' => 'Tutoring and Education', 'profession' => 'Science Tutor', 'profession_type' => 'Full-time',
                'experience' => '9', 'country' => 'Bangladesh', 'education' => 'MSc in Mathematics',
                'address' => '77 Mirpur Road, Dhaka',
                'biography' => '<p>Science tutor in Dhaka offering tutoring in physics, chemistry, biology and mathematics for all class levels.</p>',
            ],
            [
                'name' => 'Ayesha Rahman', 'email' => 'ayesha.rahman@mercibuddy.com',
                'category' => 'Cleaning', 'profession' => 'Cleaner', 'profession_type' => 'Part-time',
                'experience' => '3', 'country' => 'Switzerland', 'education' => 'Higher Secondary Certificate',
                'address' => '3 Marktplatz, Basel',
                'biography' => '<p>Professional cleaner in Basel for homes and offices. Housekeeping, mopping and deep cleaning across Basel-Stadt.</p>',
            ],
            [
                'name' => 'Priya Sharma', 'email' => 'priya.sharma@mercibuddy.com',
                'category' => 'Childcare', 'profession' => 'Nanny', 'profession_type' => 'Full-time',
                'experience' => '6', 'country' => 'France', 'education' => 'Diploma in Early Childhood Care',
                'address' => '5 Rue Saint-Malo, Rennes',
                'biography' => '<p>Caring nanny in Rennes for childcare and babysitting, including after-school care and weekend babysitting.</p>',
            ],
            [
                'name' => 'Karim Sheikh', 'email' => 'karim.sheikh@mercibuddy.com',
                'category' => 'Cooking and Catering', 'profession' => 'Cook', 'profession_type' => 'Full-time',
                'experience' => '7', 'country' => 'Bangladesh', 'state' => 'Khulna', 'city' => 'Khulna',
                'education' => 'Diploma in Culinary Arts',
                'address' => '12 KDA Avenue, Khulna',
                'biography' => '<p>Experienced cook in Khulna offering home cooking, catering and meal preparation across Khulna, Bangladesh.</p>',
            ],
            [
                'name' => 'Mizan Rahman', 'email' => 'mizan.rahman@mercibuddy.com',
                'category' => 'Electrical Services', 'profession' => 'Electrician', 'profession_type' => 'Full-time',
                'experience' => '9', 'country' => 'Bangladesh', 'state' => 'Khulna', 'city' => 'Khulna',
                'education' => 'Vocational Diploma in Electrical Work',
                'address' => '34 Sher-E-Bangla Road, Khulna',
                'biography' => '<p>Trusted electrician in Khulna for home wiring, appliance repair and electrical services across Khulna, Bangladesh.</p>',
            ],
            [
                'name' => 'Jamal Uddin', 'email' => 'jamal.uddin@mercibuddy.com',
                'category' => 'Driving and Transport', 'profession' => 'Driver', 'profession_type' => 'Full-time',
                'experience' => '11', 'country' => 'Bangladesh', 'state' => 'Khulna', 'city' => 'Khulna',
                'education' => 'Higher Secondary Certificate',
                'address' => '8 South Central Road, Khulna',
                'biography' => '<p>Reliable driver in Khulna for daily transport, airport pickup and delivery services across Khulna, Bangladesh.</p>',
            ],
            [
                'name' => 'Shirin Akter', 'email' => 'shirin.akter@mercibuddy.com',
                'category' => 'Cleaning', 'profession' => 'Cleaner', 'profession_type' => 'Part-time',
                'experience' => '4', 'country' => 'Bangladesh', 'state' => 'Khulna', 'city' => 'Khulna',
                'education' => 'Secondary School Certificate',
                'address' => '19 Talaikhana Road, Khulna',
                'biography' => '<p>Professional cleaner in Khulna for homes and offices. Housekeeping, mopping and deep cleaning across Khulna.</p>',
            ],
        ];

        $artisans = collect($artisanSeeds)->map(function (array $seed) use ($categories, $places): Artisan {
            $user = User::firstOrCreate(
                ['email' => $seed['email']],
                [
                    'name' => $seed['name'],
                    'password' => Hash::make('password'),
                    'user_type' => 2,
                    'email_verified_at' => now(),
                ],
            );

            $place = $places[$seed['country']];
            $state = $place['state'];
            $city = $place['city'];

            if (isset($seed['state'], $place['states'][$seed['state']])) {
                $state = $place['states'][$seed['state']]['state'];
                $city = $place['states'][$seed['state']]['city'];
            }

            $artisan = Artisan::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $seed['name'],
                    'profile_photo' => 'https://i.pravatar.cc/300?u='.$user->id,
                    'cover_photo' => 'https://picsum.photos/seed/cover'.$user->id.'/1280/720',
                    'category_id' => $categories[$seed['category']]->id,
                    'experience_in_year' => $seed['experience'],
                    'website' => 'https://example.com',
                    'last_education' => $seed['education'],
                    'date_of_birth' => now()->subYears(30)->format('Y-m-d'),
                    'profession' => $seed['profession'],
                    'profession_type' => $seed['profession_type'],
                    'country_id' => $place['country']->id,
                    'state_id' => $state->id,
                    'city_id' => $city->id,
                    'address' => $seed['address'],
                    'biography' => $seed['biography'],
                    'video_cv' => 'https://youtu.be/dQw4w9WgXcQ',
                ],
            );

            ContactDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => '+880170000000'.$user->id,
                    'email' => $user->email,
                    'facebook' => 'https://facebook.com/'.Str::slug($user->name),
                    'linkedin' => 'https://linkedin.com/in/'.Str::slug($user->name),
                    'whatsapp' => '+880170000000'.$user->id,
                ],
            );

            foreach (['Service by '.$user->name, 'Premium service - '.$user->name] as $title) {
                Service::firstOrCreate(
                    ['slug' => Str::slug($title).($title === 'Service by '.$user->name ? '' : '-'.$user->id)],
                    [
                        'title' => $title,
                        'user_id' => $user->id,
                        'featured_image' => 'https://picsum.photos/seed/svc'.$user->id.'/600/400',
                        'content' => '<p>This is a sample service offered by '.$user->name.'. Delivering high-quality results on time.</p>',
                    ],
                );
            }

            return $artisan;
        });

        $generalUsers = collect([
            ['General User 1', 'general1@mercibuddy.com'],
            ['General User 2', 'general2@mercibuddy.com'],
            ['General User 3', 'general3@mercibuddy.com'],
            ['General User 4', 'general4@mercibuddy.com'],
            ['General User 5', 'general5@mercibuddy.com'],
        ])->map(fn ($u) => User::firstOrCreate(
            ['email' => $u[1]],
            [
                'name' => $u[0],
                'password' => Hash::make('password'),
                'user_type' => 0,
                'email_verified_at' => now(),
            ],
        ));

        $comments = [
            'Great work by %s! Highly recommended.',
            'Very professional and delivered on time.',
            'Excellent communication throughout the project.',
            'Would definitely hire %s again.',
            'Good quality work, fair pricing.',
        ];
        $generalUsers->each(function ($user, $i) use ($artisans, $comments) {
            $artisans->each(function ($artisan, $j) use ($user, $i, $comments) {
                Comment::firstOrCreate([
                    'user_id' => $user->id,
                    'artisan_id' => $artisan->id,
                    'comment' => sprintf($comments[($i + $j) % count($comments)], $artisan->full_name),
                ]);
            });
        });

        $generalUsers->each(function ($user, $i) use ($artisans) {
            $artisans->each(function ($artisan, $j) use ($user, $i) {
                DB::table('artisan_user')->updateOrInsert(
                    ['user_id' => $user->id, 'artisan_id' => $artisan->id],
                    [
                        'reaction' => ($i + $j) % 4 === 0 ? 'dislike' : 'like',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            });
        });

        $dhaka = $places['Bangladesh'];
        $kolkata = $places['India'];
        FilterValue::firstOrCreate(
            ['user_id' => $generalUsers[0]->id],
            [
                'country_id' => $dhaka['country']->id,
                'state_id' => $dhaka['state']->id,
                'city_id' => $dhaka['city']->id,
                'category_id' => $categories->first()->id,
            ],
        );
        FilterValue::firstOrCreate(
            ['user_id' => $generalUsers[1]->id],
            [
                'country_id' => $kolkata['country']->id,
                'state_id' => $kolkata['state']->id,
                'city_id' => $kolkata['city']->id,
                'category_id' => $categories->values()->get(1)->id,
            ],
        );

        if (Setting::query()->doesntExist()) {
            Setting::create([
                'favicon' => 'favicon.ico',
                'logo' => 'logo.png',
                'og_image' => 'og.png',
                'hero_title' => 'Find skilled artisans near you',
                'title_text' => 'MerciBuddy - Connecting you with trusted local professionals',
                'meta_description' => 'MerciBuddy helps you discover and connect with skilled artisans and service providers in your area.',
                'keywords' => 'artisan, services, handyman, freelancer, mercibuddy, professionals',
                'copyright_text' => '© '.date('Y').' MerciBuddy. All rights reserved.',
            ]);
        }

        collect([
            ['About Us', 'about'],
            ['Privacy Policy', 'privacy-policy'],
            ['Terms of Use', 'terms-of-use'],
        ])->each(function ($page) {
            Page::firstOrCreate(
                ['slug' => $page[1]],
                [
                    'title' => $page[0],
                    'content' => '<p>This is the '.$page[0].' page content. Edit this from the admin panel.</p>',
                ],
            );
        });
    }
}

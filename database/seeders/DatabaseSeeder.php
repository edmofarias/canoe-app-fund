<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create Fund Managers
        $fundManagers = [
            \App\Models\FundManager::create(['name' => 'Sequoia Capital']),
            \App\Models\FundManager::create(['name' => 'Andreessen Horowitz']),
            \App\Models\FundManager::create(['name' => 'Accel Partners']),
            \App\Models\FundManager::create(['name' => 'Benchmark Capital']),
            \App\Models\FundManager::create(['name' => 'Greylock Partners']),
        ];

        // Create Companies
        $companies = [
            \App\Models\Company::create(['name' => 'Stripe']),
            \App\Models\Company::create(['name' => 'Airbnb']),
            \App\Models\Company::create(['name' => 'DoorDash']),
            \App\Models\Company::create(['name' => 'Instacart']),
            \App\Models\Company::create(['name' => 'Coinbase']),
            \App\Models\Company::create(['name' => 'Robinhood']),
            \App\Models\Company::create(['name' => 'Databricks']),
            \App\Models\Company::create(['name' => 'Figma']),
            \App\Models\Company::create(['name' => 'Notion']),
            \App\Models\Company::create(['name' => 'Discord']),
        ];

        // Create Funds with Aliases and Company Associations
        $fund1 = \App\Models\Fund::create([
            'name' => 'Sequoia Capital Fund XIV',
            'start_year' => 2018,
            'fund_manager_id' => $fundManagers[0]->id,
        ]);
        \App\Models\Alias::create(['name' => 'SC Fund 14', 'fund_id' => $fund1->id]);
        \App\Models\Alias::create(['name' => 'Sequoia XIV', 'fund_id' => $fund1->id]);
        $fund1->companies()->attach([$companies[0]->id, $companies[1]->id, $companies[4]->id]);

        $fund2 = \App\Models\Fund::create([
            'name' => 'Sequoia Capital Growth Fund V',
            'start_year' => 2020,
            'fund_manager_id' => $fundManagers[0]->id,
        ]);
        \App\Models\Alias::create(['name' => 'SC Growth V', 'fund_id' => $fund2->id]);
        $fund2->companies()->attach([$companies[2]->id, $companies[3]->id]);

        $fund3 = \App\Models\Fund::create([
            'name' => 'a16z Fund VII',
            'start_year' => 2021,
            'fund_manager_id' => $fundManagers[1]->id,
        ]);
        \App\Models\Alias::create(['name' => 'Andreessen Horowitz VII', 'fund_id' => $fund3->id]);
        \App\Models\Alias::create(['name' => 'AH Fund 7', 'fund_id' => $fund3->id]);
        $fund3->companies()->attach([$companies[4]->id, $companies[5]->id, $companies[8]->id]);

        $fund4 = \App\Models\Fund::create([
            'name' => 'a16z Crypto Fund III',
            'start_year' => 2022,
            'fund_manager_id' => $fundManagers[1]->id,
        ]);
        \App\Models\Alias::create(['name' => 'Andreessen Crypto 3', 'fund_id' => $fund4->id]);
        $fund4->companies()->attach([$companies[4]->id]);

        $fund5 = \App\Models\Fund::create([
            'name' => 'Accel Growth Fund IV',
            'start_year' => 2019,
            'fund_manager_id' => $fundManagers[2]->id,
        ]);
        \App\Models\Alias::create(['name' => 'Accel IV', 'fund_id' => $fund5->id]);
        $fund5->companies()->attach([$companies[0]->id, $companies[6]->id, $companies[7]->id]);

        $fund6 = \App\Models\Fund::create([
            'name' => 'Benchmark Fund X',
            'start_year' => 2020,
            'fund_manager_id' => $fundManagers[3]->id,
        ]);
        \App\Models\Alias::create(['name' => 'Benchmark 10', 'fund_id' => $fund6->id]);
        $fund6->companies()->attach([$companies[1]->id, $companies[9]->id]);

        $fund7 = \App\Models\Fund::create([
            'name' => 'Greylock XVI',
            'start_year' => 2021,
            'fund_manager_id' => $fundManagers[4]->id,
        ]);
        \App\Models\Alias::create(['name' => 'Greylock Partners XVI', 'fund_id' => $fund7->id]);
        \App\Models\Alias::create(['name' => 'GP Fund 16', 'fund_id' => $fund7->id]);
        $fund7->companies()->attach([$companies[2]->id, $companies[6]->id, $companies[9]->id]);

        $fund8 = \App\Models\Fund::create([
            'name' => 'Greylock Discovery Fund',
            'start_year' => 2022,
            'fund_manager_id' => $fundManagers[4]->id,
        ]);
        \App\Models\Alias::create(['name' => 'Greylock Early Stage', 'fund_id' => $fund8->id]);
        $fund8->companies()->attach([$companies[7]->id, $companies[8]->id]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- 5 Fund Managers');
        $this->command->info('- 10 Companies');
        $this->command->info('- 8 Funds with aliases and company associations');
    }
}

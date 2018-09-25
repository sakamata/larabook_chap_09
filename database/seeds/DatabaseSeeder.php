<?php

use App\Eloquent\EloquentCustomer;
use App\Eloquent\EloquentCustomerPoint;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->customers();
    }

    private function customers()
    {
        EloquentCustomer::create([
            'id'   => 1,
            'name' => 'name1',
        ]);
        EloquentCustomerPoint::create([
            'customer_id' => 1,
            'point'       => 100,
        ]);
    }
}

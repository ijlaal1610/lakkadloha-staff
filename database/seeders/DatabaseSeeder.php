<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalaryRecord;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super Admin ──────────────────────────────────────────
        $admin = User::create([
            'name'          => 'Admin User',
            'email'         => 'admin@lakkadloha.com',
            'password'      => Hash::make('password123'),
            'role'          => 'super_admin',
            'employee_id'   => 'LL0001',
            'designation'   => 'System Administrator',
            'joining_date'  => '2023-01-01',
            'is_active'     => true,
        ]);

        // ── Manager ───────────────────────────────────────────────
        $manager = User::create([
            'name'          => 'Rahul Sharma',
            'email'         => 'manager@lakkadloha.com',
            'password'      => Hash::make('password123'),
            'role'          => 'manager',
            'employee_id'   => 'LL0002',
            'phone'         => '+91 9876543210',
            'designation'   => 'Store Manager',
            'joining_date'  => '2023-03-15',
            'is_active'     => true,
        ]);

        // ── Staff Members ─────────────────────────────────────────
        $staffData = [
            ['Priya Verma',   'staff1@lakkadloha.com', 'LL0003', '+91 9876543211', 'Sales Executive'],
            ['Amit Kumar',    'staff2@lakkadloha.com', 'LL0004', '+91 9876543212', 'Sales Executive'],
            ['Sunita Devi',   'staff3@lakkadloha.com', 'LL0005', '+91 9876543213', 'Store Assistant'],
        ];

        $staffMembers = [];
        foreach ($staffData as $i => [$name, $email, $empId, $phone, $desig]) {
            $staffMembers[] = User::create([
                'name'          => $name,
                'email'         => $email,
                'password'      => Hash::make('password123'),
                'role'          => 'staff',
                'employee_id'   => $empId,
                'phone'         => $phone,
                'designation'   => $desig,
                'joining_date'  => now()->subMonths(rand(3, 18))->format('Y-m-d'),
                'is_active'     => true,
            ]);
        }

        // ── Products ──────────────────────────────────────────────
        $productsData = [
            ['Teak Wood Plank 6ft',         120, 20,  'Premium teak wood planks, 6 feet length'],
            ['Teak Wood Plank 4ft',          85, 15,  'Teak wood planks, 4 feet'],
            ['Mango Wood Beam 8ft',          60, 10,  'Solid mango wood beams for furniture'],
            ['Sheesham Timber 10ft',         40,  8,  'Premium sheesham/rosewood timber'],
            ['Pine Wood Board',             200, 30,  'Pine wood boards for light work'],
            ['Iron Angle 40x40',            150, 25,  'Mild steel angle iron 40x40mm'],
            ['MS Flat Bar 50x6',            100, 20,  'Mild steel flat bar 50x6mm per meter'],
            ['Steel Pipe 1 inch',            75, 15,  'GI steel pipe 1 inch diameter'],
            ['Steel Rod 10mm',              300, 50,  'TMT steel rod 10mm'],
            ['Iron Channel 100mm',           50, 10,  'MS C-channel 100mm'],
        ];

        $products = [];
        foreach ($productsData as [$name, $stock, $threshold, $notes]) {
            $product = Product::create([
                'name'                => $name,
                'current_stock'       => $stock,
                'low_stock_threshold' => $threshold,
                'notes'               => $notes,
                'created_by'          => $admin->id,
                'is_active'           => true,
            ]);

            // Initial stock movement
            StockMovement::create([
                'product_id'   => $product->id,
                'user_id'      => $admin->id,
                'type'         => 'add',
                'quantity'     => $stock,
                'stock_before' => 0,
                'stock_after'  => $stock,
                'notes'        => 'Initial stock setup',
            ]);

            $products[] = $product;
        }

        // ── Sales (last 60 days) ───────────────────────────────────
        $allStaff = collect(array_merge([$manager], $staffMembers));
        $prices = [450, 350, 280, 600, 90, 120, 85, 95, 75, 340];

        for ($day = 60; $day >= 0; $day--) {
            $date = now()->subDays($day);
            $dailySaleCount = rand(2, 8);

            for ($s = 0; $s < $dailySaleCount; $s++) {
                $prodIdx = array_rand($products);
                $product = $products[$prodIdx];
                $staffMember = $allStaff->random();
                $qty = rand(1, 5);
                $price = $prices[$prodIdx] * (rand(90, 115) / 100);
                $price = round($price, 2);

                if ($product->current_stock < $qty) continue;

                $sale = Sale::create([
                    'sale_number'   => 'LL' . $date->format('Ymd') . str_pad($s + 1, 4, '0', STR_PAD_LEFT),
                    'product_id'    => $product->id,
                    'staff_id'      => $staffMember->id,
                    'quantity'      => $qty,
                    'selling_price' => $price,
                    'total_amount'  => round($qty * $price, 2),
                    'status'        => rand(1, 10) > 1 ? 'completed' : 'cancelled',
                    'customer_name' => rand(0, 1) ? fake()->name() : null,
                    'sold_at'       => $date->copy()->setTime(rand(9, 18), rand(0, 59)),
                ]);

                if ($sale->status === 'completed') {
                    $before = $product->current_stock;
                    $product->decrement('current_stock', $qty);
                    StockMovement::create([
                        'product_id'     => $product->id,
                        'user_id'        => $staffMember->id,
                        'type'           => 'sale',
                        'quantity'       => $qty,
                        'stock_before'   => $before,
                        'stock_after'    => $product->current_stock,
                        'reference_type' => Sale::class,
                        'reference_id'   => $sale->id,
                        'notes'          => "Sale #{$sale->sale_number}",
                    ]);
                }
            }
        }

        // ── Attendance (last 30 days) ─────────────────────────────
        $allWorkingStaff = collect(array_merge([$manager], $staffMembers));
        for ($day = 30; $day >= 0; $day--) {
            $date = now()->subDays($day)->toDateString();
            $dayOfWeek = now()->subDays($day)->dayOfWeek;
            if (in_array($dayOfWeek, [0, 6])) continue; // Skip weekends

            foreach ($allWorkingStaff as $member) {
                $roll = rand(1, 10);
                $status = $roll >= 9 ? 'absent' : ($roll >= 7 ? 'late' : 'present');
                Attendance::create([
                    'user_id'    => $member->id,
                    'date'       => $date,
                    'time_in'    => $status !== 'absent' ? ($status === 'late' ? '10:' . rand(15, 59) . ':00' : '09:' . rand(0, 15) . ':00') : null,
                    'time_out'   => $status !== 'absent' ? '18:' . rand(0, 30) . ':00' : null,
                    'status'     => $status,
                    'marked_by'  => $admin->id,
                    'ip_address' => '127.0.0.1',
                ]);
            }
        }

        // ── Salary Records ────────────────────────────────────────
        $allWorkers = collect(array_merge([$manager], $staffMembers));
        foreach ($allWorkers as $worker) {
            $baseSalary = $worker->role === 'manager' ? 25000 : rand(12000, 18000);

            for ($month = 2; $month >= 0; $month--) {
                $recordDate = now()->subMonths($month)->endOfMonth()->format('Y-m-d');
                SalaryRecord::create([
                    'employee_id'    => $worker->id,
                    'processed_by'   => $admin->id,
                    'type'           => 'salary',
                    'amount'         => $baseSalary,
                    'record_date'    => $recordDate,
                    'payment_method' => rand(0, 1) ? 'bank_transfer' : 'upi',
                    'notes'          => now()->subMonths($month)->format('F Y') . ' salary',
                ]);
            }

            // Occasional advance
            if (rand(0, 1)) {
                SalaryRecord::create([
                    'employee_id'    => $worker->id,
                    'processed_by'   => $admin->id,
                    'type'           => 'advance',
                    'amount'         => rand(2000, 5000),
                    'record_date'    => now()->subDays(rand(5, 20))->format('Y-m-d'),
                    'payment_method' => 'cash',
                    'notes'          => 'Salary advance',
                ]);
            }
        }

        $this->command->info('✅ Lakkad Loha seeded successfully!');
        $this->command->info('   Admin:   admin@lakkadloha.com / password123');
        $this->command->info('   Manager: manager@lakkadloha.com / password123');
        $this->command->info('   Staff:   staff1@lakkadloha.com / password123');
    }
}

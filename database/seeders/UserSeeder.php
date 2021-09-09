<?php

namespace Database\Seeders;

use App\Http\Controllers\EthereumController;
use App\Http\Controllers\GethController;
use App\Models\EthereumWallet;
use App\Models\Group;
use App\Models\GroupJoin;
use App\Models\User;
use App\Models\UserLogJoin;
use App\Models\UserLogLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'all-privileges']);
        Permission::create(['name' => 'board-create']);
        Permission::create(['name' => 'board-read']);
        Permission::create(['name' => 'board-update']);
        Permission::create(['name' => 'board-delete']);
        Permission::create(['name' => 'board_article-create']);
        Permission::create(['name' => 'board_article-read']);
        Permission::create(['name' => 'board_article-update']);
        Permission::create(['name' => 'board_article-delete']);

        $role1 = Role::create(['name' => '관리자']);
        $role2 = Role::create(['name' => '회원']);

        $role1->givePermissionTo('all-privileges');

        $group = Group::create([
            'label' => 1,
            'layer' => 1,
            'member' => 0
        ]);

        $random = Str::random(12);

        $user = User::create([
            'username' => 'admin',
            'email' => 'admin@coin.com',
            'nickname' => '관리자',
            'name' => '이아무개',
            'point_1' => 0,
            'password' => bcrypt("1234"),
            'referer_code' => $random,
            'group_id' => $group->id
        ]);

        $user->assignRole($role1);

        GroupJoin::create([
            'group_id' => $group->id,
            'user_id' => $user->id
        ]);

        UserLogLevel::create([
            'user_id' => $user->id,
            'level' => 1
        ]);

        $newWallet = EthereumController::getNewWallet();
        EthereumWallet::create([
            'user_id' => $user->id,
            'address' => $newWallet['address'],
            'private_key' => $newWallet['private_key'],
            'balance' => 0,
            'token_balance' => 0
        ]);
    }
}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllTables extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        CreateAllTables::create_tablegroups_table();
        CreateAllTables::create_pallets_table();
        CreateAllTables::create_people_table();
        CreateAllTables::create_users_table();
        CreateAllTables::create_divisions_table();
        CreateAllTables::create_sections_table();
        CreateAllTables::create_categories_table();
        CreateAllTables::create_bookfairs_table();
        CreateAllTables::create_attendances_table();
        CreateAllTables::create_statistics_table();
        CreateAllTables::create_privileges_table();
        CreateAllTables::create_privilege_user_table();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('privilege_user');
        Schema::dropIfExists('privileges');
        Schema::dropIfExists('statistics');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('bookfairs');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('people');
        Schema::dropIfExists('pallets');
        Schema::dropIfExists('table_groups');
    }

    public function create_attendances_table() {
        Schema::create('attendances', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('bookfair_id')->unsigned();
            $table->date('day');
            $table->integer('start_hr')->unsigned();
            $table->integer('end_hr')->unsigned();
            $table->integer('attendance');
            $table->foreign('bookfair_id')->references('id')->on('bookfairs')->onDelete('cascade');
        });
    }

    public function create_bookfairs_table() {
        Schema::create('bookfairs', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('year')->unsigned();
            $table->string('season', 7);
            $table->string('location', 100)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration')->unsigned()->default(3);
            $table->boolean('bag_sale')->default(true);
            $table->string('fri_open',4)->nullable();
            $table->string('fri_close',4)->nullable();
            $table->string('sat_open',4)->nullable();
            $table->string('sat_close',4)->nullable();
            $table->string('sun_open',4)->nullable();
            $table->string('sun_close',4)->nullable();
            $table->boolean('locked')->default(true);
            $table->unique(array('year', 'season'), 'uq_bookfair_season');
        });
    }

    public function create_categories_table() {
        Schema::create('categories', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('section_id')->unsigned();
            $table->string('name', 100);
            $table->string('label', 8)->nullable();
            $table->integer('pallet_id')->unsigned()->nullable();
            $table->integer('tablegroup_id')->unsigned()->nullable(); // foreign key to table_groups
            $table->string('measure')->default('table');
            $table->integer('pallet_loading')->default('56'); 
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
        });
    }

    public function create_divisions_table() {
        Schema::create('divisions', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 100)->unique();
            $table->integer('head_person_id')->unsigned()->nullable();
            if (Schema::hasTable('people')) {
                $table->foreign('head_person_id')->references('id')->on('people')->onDelete('cascade');
            }
        });
    }

    public function create_pallets_table() {
        Schema::create('pallets', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 50)->unique();
        });
    }
     
    public function create_people_table() {
        Schema::create('people', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            // Identification
            $table->string('title', 3)->nullable();
            $table->string('first_name', 64);
            $table->string('last_name', 64);
            $table->date('dob')->nullable();
            $table->string('gender', 7);
            $table->string('photo_filename')->nullable();
            // Contact Information
            $table->string('email', 128)->nullable()->unique();
            $table->string('mobile_phone', 11)->nullable();
            $table->string('home_phone', 11)->nullable();
            $table->string('unit_no', 10)->nullable();
            $table->string('street_no', 10)->nullable();
            $table->string('street_name', 64)->nullable();
            $table->string('street_type', 10)->nullable();
            $table->string('suburb', 64)->nullable();
            $table->string('state', 4)->nullable();
            $table->timestamps();
            $table->unique(array('last_name', 'first_name'), 'uq_fullname');
        });
    }

    public function create_privileges_table() {
        Schema::create('privileges', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 100)->unique();
        });
    }

    public function create_privilege_user_table() {
        Schema::create('privilege_user', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('privilege_id')->unsigned();
            $table->timestamps();
        });
    }

    public function create_takings_table() {
        Schema::create('takings', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increment('id');
            $table->integer('bookfair_id')->unsigned();
            $table->string('day', '10');
            $table->float('amount')->unsigned();
            $table->foreign('bookfair_id')->references('id')->on('bookfairs')->onDelete('cascade');
        });
    }

    public function create_statistics_table() {
        Schema::create('statistics', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('bookfair_id')->unsigned();
            $table->integer('section_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->integer('tablegroup_id')->unsigned()->nullable();
            $table->integer('palletgroup_id')->unsigned()->nullable();
            $table->boolean('allocate');
            $table->boolean('track');
            $table->string('label', 5)->nullable();
            $table->string('name', 100);
            $table->integer('target')->unsigned();
            $table->integer('packed')->unsigned()->nullable(); // boxes packed
            $table->decimal('suggested', 6, 2)->unsigned()->nullable(); // suggested table count
            $table->decimal('allocated', 6, 2)->unsigned()->nullable(); // tables allocated
            $table->string('measure', 7)->default('box');
            $table->decimal('delivered', 6, 2)->unsigned()->default(0); // boxes
            $table->decimal('loading', 6, 2)->unsigned()->default(0);  // Boxes per table
            $table->decimal('start_display', 6, 2)->unsigned()->default(0);
            $table->decimal('start_reserve', 6, 2)->unsigned()->default(0);
            $table->decimal('fri_extras', 6, 2)->unsigned()->default(0);
            $table->decimal('fri_end_display', 6, 2)->unsigned()->default(0);
            $table->decimal('fri_end_reserve', 6, 2)->unsigned()->default(0);
            $table->decimal('fri_sold', 6, 2)->unsigned()->default(0);
            $table->decimal('sat_extras', 6, 2)->unsigned()->default(0);
            $table->decimal('sat_end_display', 6, 2)->unsigned()->default(0);
            $table->decimal('sat_end_reserve', 6, 2)->unsigned()->default(0);
            $table->decimal('sat_sold', 6, 2)->unsigned()->default(0);
            $table->decimal('sun_extras', 6, 2)->unsigned()->default(0);
            $table->decimal('sun_end_display', 6, 2)->unsigned()->default(0);
            $table->decimal('sun_end_reserve', 6, 2)->unsigned()->default(0);
            $table->decimal('sun_sold', 6, 2)->unsigned()->default(0);
            $table->decimal('end_extras', 6, 2)->unsigned()->default(0);
            $table->decimal('end_display', 6, 2)->unsigned()->default(0);
            $table->decimal('end_reserve', 6, 2)->unsigned()->default(0);
            $table->decimal('bag_sold', 6, 2)->unsigned()->default(0);
            $table->decimal('total_stock', 6, 2)->unsigned()->default(0);
            $table->decimal('total_sold', 6, 2)->unsigned()->default(0);
            $table->decimal('total_unsold', 6, 2)->unsigned()->default(0);
            $table->unique(array('category_id', 'bookfair_id'), 'uq_statistic_category');
            $table->index('bookfair_id');
            $table->index('section_id');
            $table->index('tablegroup_id');
            $table->foreign('bookfair_id')->references('id')->on('bookfairs')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            $table->foreign('tablegroup_id')->references('id')->on('table_groups')->onDelete('set null');
        });
    }

    public function create_sections_table() {
        Schema::create('sections', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 100)->unique();
            $table->integer('division_id')->unsigned();
        });
    }

    public function create_tablegroups_table() {
        Schema::create('table_groups', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 16)->unique();
            $table->string('location', 64);
            $table->string('room', 64);
            $table->integer('tables')->unsigned();
            $table->string('table_type', 6)->default('Light');
        });
    }

    public function create_users_table() {
        Schema::create('users', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('id', 8)->primary();
            $table->string('person_id');
            $table->string('password', 64)->nullable();
            $table->timestamp('last_login')->nullable();
            $table->integer('attempts')->unsigned()->default(0);
            $table->timestamp('last_reset');
            $table->boolean('locked')->default(false);
            $table->timestamps();
        });
    }

}

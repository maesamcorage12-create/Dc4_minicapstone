<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;use App\Models\User;use App\Models\Destination;use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder {public function run():void{
User::updateOrCreate(['email'=>'admin@tripcraft.test'],['name'=>'TripCraft Admin','password'=>Hash::make('Password123!'),'role'=>'admin']);User::updateOrCreate(['email'=>'traveler@tripcraft.test'],['name'=>'Demo Traveler','password'=>Hash::make('Password123!'),'role'=>'traveler']);
$rows=[
['Chocolate Hills','Carmen, Bohol','Nature','See Bohol’s iconic rolling limestone hills from scenic viewpoints.',500,['sightseeing','photography'],'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=85'],
['Alona Beach','Panglao, Bohol','Beach','A lively beach base for swimming, dining, and island excursions.',800,['beach','water activities'],'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=1200&q=85'],
['Loboc River','Loboc, Bohol','Nature','Enjoy lush river scenery and local river-cruise experiences.',750,['river cruise','food'],'https://images.unsplash.com/photo-1437482078695-73f5ca6c96e2?auto=format&fit=crop&w=1200&q=85'],
['Hinagdanan Cave','Dauis, Bohol','Adventure','Explore a limestone cave with a natural underground pool.',400,['cave','adventure'],'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=85'],
['Philippine Tarsier Sanctuary','Corella, Bohol','Wildlife','Learn about the tiny Philippine tarsier and its protected habitat.',350,['wildlife','conservation'],'https://images.unsplash.com/photo-1516934024742-b461fba47600?auto=format&fit=crop&w=1200&q=85'],
['Bilar Man-Made Forest','Bilar, Bohol','Nature','Travel through a shaded mahogany forest on the island’s interior route.',150,['forest','photography'],'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1200&q=85'],
['Baclayon Church','Baclayon, Bohol','Heritage','Discover one of Bohol’s best-known historic church landmarks.',200,['history','architecture'],'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1200&q=85'],
['Balicasag Island','Off Panglao, Bohol','Beach','A popular excursion for snorkeling and observing marine life responsibly.',1800,['island hopping','snorkeling'],'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=1200&q=85'],
['Quinale Beach','Anda, Bohol','Beach','Relax on a stretch of pale sand and clear water in eastern Bohol.',300,['beach','relaxation'],'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=85'],
['Pamilacan Island','Off Baclayon, Bohol','Island','Visit a small island known for coastal scenery and marine excursions.',1600,['island hopping','boat trip'],'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?auto=format&fit=crop&w=1200&q=85'],
['Can-umantad Falls','Candijay, Bohol','Nature','Visit a dramatic waterfall surrounded by green countryside.',600,['waterfall','outdoors'],'https://images.unsplash.com/photo-1432405972618-c60b0225b8f9?auto=format&fit=crop&w=1200&q=85'],
['Mag-Aso Falls','Antequera, Bohol','Adventure','A green, peaceful waterfall destination for nature lovers.',300,['waterfall','adventure'],'https://images.unsplash.com/photo-1511497584788-876760111969?auto=format&fit=crop&w=1200&q=85'],
['Dumaluan Beach','Panglao, Bohol','Beach','Spend a relaxed day by the sand and shallow turquoise water.',500,['beach','family'],'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=85'],
['Blood Compact Shrine','Tagbilaran area, Bohol','Heritage','Learn about a significant event in Philippine and Boholano history.',150,['history','culture'],'https://images.unsplash.com/photo-1566127992631-137a642a90f4?auto=format&fit=crop&w=1200&q=85'],
['Bohol Python and Wildlife Park','Albuquerque, Bohol','Wildlife','A wildlife attraction; check current operating details before visiting.',300,['wildlife','family'],'https://images.unsplash.com/photo-1564349683136-77e08dba1ef7?auto=format&fit=crop&w=1200&q=85']
];foreach($rows as $r)Destination::updateOrCreate(['name'=>$r[0]],['location'=>$r[1],'category'=>$r[2],'description'=>$r[3],'avg_daily_cost'=>$r[4],'tags'=>$r[5],'image_url'=>$r[6],'is_active'=>true]);
}}

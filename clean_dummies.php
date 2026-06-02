<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dummyClientIds = range(13, 36);

$aguardando = App\Models\User::where('name', 'Aguardando Cliente')->first();

foreach($dummyClientIds as $id) {
    $user = App\Models\User::find($id);
    if ($user) {
        App\Models\Pasta::where('cliente_id', $user->id)->update([
            'cliente_id' => null,
            'identificador_cliente' => $user->name
        ]);
        
        if ($aguardando) {
            App\Models\Marco::where('user_id', $user->id)->update([
                'user_id' => $aguardando->id
            ]);
        }
        
        $user->delete();
        echo "Deleted dummy user: {$user->name}\n";
    }
}

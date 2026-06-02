<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function generateDummyCpf($name) {
    $hash = md5($name);
    $digits = [];
    for ($i = 0; $i < 9; $i++) {
        $digits[] = hexdec($hash[$i]) % 10;
    }
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += $digits[$i] * (10 - $i);
    }
    $r = $sum % 11;
    $dv1 = ($r < 2) ? 0 : 11 - $r;
    $digits[] = $dv1;
    $sum = 0;
    for ($i = 0; $i < 10; $i++) {
        $sum += $digits[$i] * (11 - $i);
    }
    $r = $sum % 11;
    $dv2 = ($r < 2) ? 0 : 11 - $r;
    $digits[] = $dv2;
    return implode('', $digits);
}

$aguardando = App\Models\User::firstOrCreate(
    ['name' => 'Aguardando Cliente'],
    [
        'email' => 'aguardando' . rand(100, 999) . '@getectopografia.com.br',
        'password' => bcrypt('password'),
        'role' => 'client',
        'approved' => 1,
        'tipo' => 'PF',
        'cpf' => generateDummyCpf('Aguardando Cliente')
    ]
);

$badNames = ['ORÇANDO', 'ORCANDO', 'DIVISA', 'PENDENTE', 'OK'];
foreach($badNames as $bn) {
    $badUsers = App\Models\User::where('name', 'like', "%$bn%")->get();
    foreach($badUsers as $badUser) {
        if ($badUser->id === $aguardando->id) continue;
        
        App\Models\Pasta::where('cliente_id', $badUser->id)->update(['cliente_id' => $aguardando->id]);
        App\Models\Marco::where('user_id', $badUser->id)->update(['user_id' => $aguardando->id]);
        
        $badUser->delete();
    }
}
echo "Database updated and bad users merged to Aguardando Cliente.\n";

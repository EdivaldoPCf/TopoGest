<?php
function parseFolderOwner($folderName) {
    $imovelNome = $folderName;
    $clienteNome = null;

    if (preg_match('/^(.*?)\s*\((.*?)\)$/', $folderName, $matches)) {
        $imovelNome = trim($matches[1]);
        $ownerPart = trim($matches[2]);
        $clienteNome = preg_replace('/\b(desmembramento|gleba|lote|proprietario|dono)\b/i', '', $ownerPart);
        $clienteNome = trim(preg_replace('/\s+/', ' ', $clienteNome));
    } elseif (preg_match('/^(.*?)\s*-\s*(.*?)$/', $folderName, $matches)) {
        $part1 = trim($matches[1]);
        $part2 = trim($matches[2]);
        if (preg_match('/\b(orçando|orcando|divisa|ok|pendente)\b/i', $part2)) {
            if (preg_match('/^(Desmembramento|Lote|Gleba|Fazenda|Área|Area)\s+(.*?)$/i', $part1, $subMatches)) {
                $imovelNome = $part1;
                $clienteNome = trim($subMatches[2]);
            } else {
                $imovelNome = $part1;
                $clienteNome = $part1;
            }
        } else {
            $imovelNome = $part1;
            $clienteNome = $part2;
        }
    }

    if (strpos($folderName, ' - ') !== false) {
        $parts = array_map('trim', explode(' - ', $folderName));
        $lastPart = end($parts);
        if (!preg_match('/\b(orçando|orcando|divisa|ok|pendente)\b/i', $lastPart)) {
            $clienteNome = $lastPart;
        }
    }

    if (empty($clienteNome)) {
        if (strpos($folderName, '_') !== false) {
            $parts = explode('_', $folderName);
            $clienteNome = trim($parts[0]);
            $imovelNome = str_replace('_', ' ', $folderName);
        } else {
            $clienteNome = $folderName;
        }
    }

    return [
        'imovel' => $imovelNome ?: $folderName,
        'cliente' => $clienteNome ?: 'Cliente Importado',
    ];
}

$testCases = [
    'LOTE 65 - ORÇANDO',
    'DESMEMBRAMENTO CLEBER - ORÇANDO',
    'FAZENDA BELA ALIANÇA - DIVISA',
    'LOTE 76_78 GL_F PAD PEIXOTO - IRMÃO DO NONATO CRUZ',
    'LUCAS _PA WILSON LOPES - 218-219'
];

foreach ($testCases as $tc) {
    print_r(parseFolderOwner($tc));
}

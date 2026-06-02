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
        if (preg_match('/(?:^|\s|_|-)(orçando|orcando|divisa|ok|pendente)(?:\s|_|-|$)/iu', $part2)) {
            $imovelNome = $part1;
            $clienteNome = 'Aguardando Cliente';
        } else {
            $imovelNome = $part1;
            $clienteNome = $part2;
        }
    }

    // Check last part if split by ' - '
    if (strpos($folderName, ' - ') !== false) {
        $parts = array_map('trim', explode(' - ', $folderName));
        $lastPart = end($parts);
        if (preg_match('/(?:^|\s|_|-)(orçando|orcando|divisa|ok|pendente)(?:\s|_|-|$)/iu', $lastPart)) {
            $clienteNome = 'Aguardando Cliente';
        } else {
            $clienteNome = $lastPart;
        }
    }

    if (empty($clienteNome) || $clienteNome === $folderName) {
        if (strpos($folderName, '_') !== false) {
            $parts = explode('_', $folderName);
            $clienteNome = trim($parts[0]);
            $imovelNome = str_replace('_', ' ', $folderName);
        } else {
            if (preg_match('/(?:^|\s|_|-)(orçando|orcando|divisa|ok|pendente)(?:\s|_|-|$)/iu', $folderName)) {
                $clienteNome = 'Aguardando Cliente';
            } else {
                $clienteNome = $folderName;
            }
        }
    }

    return [
        'imovel' => $imovelNome ?: $folderName,
        'cliente' => $clienteNome ?: 'Aguardando Cliente',
    ];
}

$testCases = [
    'LOTE 65 - ORÇANDO',
    'DESMEMBRAMENTO CLEBER - ORÇANDO',
    'FAZENDA BELA ALIANÇA - DIVISA',
    'LOTE 76_78 GL_F PAD PEIXOTO - IRMÃO DO NONATO CRUZ',
    'LUCAS _PA WILSON LOPES - 218-219',
    'Fazenda xyz (Joaozinho)',
    'Imovel pendente',
    'Imovel sem hifen orcando'
];

foreach ($testCases as $tc) {
    print_r(parseFolderOwner($tc));
}

<?php
function parseFolderOwner($folderName) {
    $imovelNome = $folderName;
    $clienteNome = null;

    if (preg_match('/^(.*?)\s*\((.*?)\)$/', $folderName, $matches)) {
        // ...
    } elseif (preg_match('/^(.*?)\s*-\s*(.*?)$/', $folderName, $matches)) {
        $part1 = trim($matches[1]);
        $part2 = trim($matches[2]);
        echo "Matched part1='$part1', part2='$part2'\n";
        if (preg_match('/\b(orçando|orcando|divisa|ok|pendente)\b/i', $part2)) {
            echo "Matched orçando in part2!\n";
            if (preg_match('/^(Desmembramento|Lote|Gleba|Fazenda|Área|Area)\s+(.*?)$/i', $part1, $subMatches)) {
                $imovelNome = $part1;
                $clienteNome = trim($subMatches[2]);
                echo "Submatched! cliente=$clienteNome\n";
            } else {
                $imovelNome = $part1;
                $clienteNome = $part1;
                echo "No submatch. cliente=$clienteNome\n";
            }
        } else {
            $imovelNome = $part1;
            $clienteNome = $part2;
        }
    }

    echo "After first block: cliente=$clienteNome\n";

    if (strpos($folderName, ' - ') !== false) {
        $parts = array_map('trim', explode(' - ', $folderName));
        $lastPart = end($parts);
        echo "lastPart='$lastPart'\n";
        if (!preg_match('/\b(orçando|orcando|divisa|ok|pendente)\b/i', $lastPart)) {
            $clienteNome = $lastPart;
            echo "Overwritten cliente=$clienteNome\n";
        }
    }

    echo "After second block: cliente=$clienteNome\n";

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

print_r(parseFolderOwner('LOTE 65 - ORÇANDO'));

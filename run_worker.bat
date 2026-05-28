@echo off
setlocal enabledelayedexpansion
title TopoGest Sync Worker
color 0A
echo ===================================================
echo     TopoGest - Robô Sincronizador Local            
echo     NÃO FECHE ESTA JANELA!                         
echo ===================================================
cd /d e:\TopoGest

:loop
if exist storage\sync_request.txt (
    echo [!time!] Sincronizacao geral solicitada pelo painel...
    del storage\sync_request.txt
    php artisan pastas:sync-pendentes
    echo [!time!] Sincronizacao finalizada!
)

if exist storage\sync_request_pasta.txt (
    set PASTA_ID=
    for /f "usebackq delims=" %%a in ("storage\sync_request_pasta.txt") do set PASTA_ID=%%a
    if not "!PASTA_ID!"=="" (
        echo [!time!] Sincronizacao especifica solicitada para pasta !PASTA_ID!...
        del storage\sync_request_pasta.txt
        php artisan pastas:sync-pendentes --pasta_id=!PASTA_ID!
        echo [!time!] Sincronizacao especifica finalizada!
    ) else (
        del storage\sync_request_pasta.txt
    )
)

timeout /t 3 > nul
goto loop

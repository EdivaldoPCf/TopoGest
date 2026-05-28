Set WshShell = CreateObject("Wscript.Shell")
WshShell.Run "cmd.exe /c cd /d e:\TopoGest && run_worker.bat", 0, false

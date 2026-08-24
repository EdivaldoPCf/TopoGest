const { exec } = require('child_process');
const path = require('path');
const fs = require('fs');

// Obtém o caminho absoluto do projeto
const projectDir = path.resolve(__dirname);

// Cria um arquivo .bat lançador para o Registry chamar
const batContent = `
@echo off
cd /d "${projectDir}"
npm start -- "%~1"
`;
const batPath = path.join(projectDir, 'run-topogest.bat');
fs.writeFileSync(batPath, batContent);

// Comandos do Regedit (Usando HKCU para não precisar de permissão de Administrador)
const commands = [
    // Cria a opção no menu de contexto das pastas
    `reg add "HKCU\\Software\\Classes\\Directory\\shell\\TopoGest" /ve /d "Sincronizar com TopoGest" /f`,
    
    // Configura o comando a ser executado ao clicar
    `reg add "HKCU\\Software\\Classes\\Directory\\shell\\TopoGest\\command" /ve /d "\\"${batPath}\\" \\"%1\\"" /f`
];

console.log('Injetando chaves no Registro do Windows...');

let completed = 0;
commands.forEach(cmd => {
    exec(cmd, (error, stdout, stderr) => {
        if (error) {
            console.error(`Erro ao executar: ${cmd}`);
            console.error(error);
        } else {
            console.log(`Sucesso: ${cmd}`);
        }
        
        completed++;
        if (completed === commands.length) {
            console.log('\n✅ Instalação do Menu de Contexto concluída!');
            console.log('Vá em qualquer pasta do seu Windows, clique com o botão direito e veja a mágica!');
        }
    });
});

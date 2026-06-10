const { app, BrowserWindow, Tray, Menu, ipcMain, dialog, nativeImage } = require('electron');
const path = require('path');
const chokidar = require('chokidar');
const axios = require('axios');
const FormData = require('form-data');
const fs = require('fs');

// Variáveis para import dinâmico do electron-store
let Store;
let store;
app.commandLine.appendSwitch('disable-gpu-shader-disk-cache');

let mainWindow = null;
let trayWindow = null;
let tray = null;
let watchers = {}; // Armazena instâncias do chokidar por caminho { 'C:\Pasta': watcherInstance }
let apiToken = null;
let user = null;
let folders = []; // Array of { path, ano, categoria }

// TODO: Permitir configuração customizada ou usar URL do produção
const BASE_URL = 'http://localhost:25565'; 
const API_LOGIN = `${BASE_URL}/api/v1/desktop/login`;
const API_SYNC = `${BASE_URL}/api/v1/desktop/sync-file`;

// Lendo caminho que vem do Menu de Contexto (Botão Direito)
let contextFolder = '';
let args = process.argv.slice(1);
if (args[0] === '.') args = args.slice(1);
if (args.length > 0) contextFolder = args.join(' ');

function createWindow() {
    // Janela Principal (Dashboard)
    mainWindow = new BrowserWindow({
        width: 1000,
        height: 700,
        show: false, // Começa invisível, abre só pelo menu ou tray
        frame: false,
        resizable: true,
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: false
        }
    });

    mainWindow.loadFile('index.html');

    // Janela da Bandeja Oculta (Tray)
    trayWindow = new BrowserWindow({
        width: 350,
        height: 450,
        show: false,
        frame: false,
        fullscreenable: false,
        resizable: false,
        transparent: true,
        skipTaskbar: true,
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: false
        }
    });
    
    trayWindow.loadFile('tray.html');

    // Esconde a trayWindow quando ela perde o foco
    trayWindow.on('blur', () => {
        trayWindow.hide();
    });

    mainWindow.webContents.on('did-finish-load', () => {
        // Manda estado de autenticação para a UI
        mainWindow.webContents.send('auth-status', { isAuth: !!apiToken, user });

        // Se veio do menu de contexto e já estamos logados, mostra o popup de adicionar pasta e a janela principal
        if (apiToken && contextFolder && fs.existsSync(contextFolder)) {
            const exists = folders.find(f => f.path === contextFolder);
            if (!exists) {
                mainWindow.show();
                mainWindow.webContents.send('folder-selected', contextFolder);
            }
        } else if (!apiToken) {
            // Se não tem login, força a mostrar a janela principal de cara
            mainWindow.show();
        }
    });
}

// Inicializa Bandeja Oculta
async function initTray() {
    let icon;
    try {
        icon = await app.getFileIcon(process.execPath, { size: 'small' });
    } catch(e) {
        icon = nativeImage.createEmpty();
    }
    
    tray = new Tray(icon);
    tray.setToolTip('TopoGest Drive');
    
    tray.on('click', (event, bounds) => {
        // Se não tiver token, mostra o login de qualquer jeito
        if (!apiToken) {
            mainWindow.show();
            return;
        }

        if (trayWindow.isVisible()) {
            trayWindow.hide();
        } else {
            const { x, y } = bounds;
            const { height, width } = trayWindow.getBounds();
            
            // Posiciona acima do relógio
            trayWindow.setBounds({
                x: x - (width / 2),
                y: y - height - 10,
                width,
                height
            });
            trayWindow.show();
        }
    });

    const contextMenu = Menu.buildFromTemplate([
        { label: 'Mostrar TopoGest', click: () => mainWindow.show() },
        { label: 'Sair', click: () => { app.isQuiting = true; app.quit(); } }
    ]);
    tray.setContextMenu(contextMenu);
}

// ----- ROTINAS DE SINCRONIZAÇÃO E PASTAS -----

function startAllWatchers() {
    // Para todos antes de reiniciar
    Object.values(watchers).forEach(w => w.close());
    watchers = {};

    folders.forEach(folder => {
        if (fs.existsSync(folder.path)) {
            startWatcherForFolder(folder);
        } else {
            console.warn('Pasta mapeada não encontrada localmente: ', folder.path);
        }
    });
}

function startWatcherForFolder(folderObj) {
    const w = chokidar.watch(folderObj.path, {
        persistent: true,
        ignoreInitial: true,
        ignored: /(^|[\/\\])\../,
        usePolling: true,
        interval: 1000
    });

    w.on('add', filePath => handleFileEvent('add', filePath, folderObj))
     .on('change', filePath => handleFileEvent('change', filePath, folderObj))
     .on('unlink', filePath => handleFileEvent('unlink', filePath, folderObj));

    watchers[folderObj.path] = w;
}

async function handleFileEvent(action, filePath, folderObj) {
    if (!apiToken) return;

    const relativePath = path.relative(folderObj.path, filePath);
    
    // Atualiza UI pra mostrar progresso simulado (uploading...)
    if (mainWindow && !mainWindow.isDestroyed()) {
        mainWindow.webContents.send('sync-progress', {
            file: path.basename(filePath),
            folder: folderObj.path.split('\\').pop(),
            progress: 50,
            status: 'Enviando...'
        });
    }

    try {
        const form = new FormData();
        form.append('relative_path', relativePath);
        form.append('action', action);
        
        // Passa os metadados estruturais caso a pasta exija
        form.append('override_ano', folderObj.ano);
        form.append('override_categoria', folderObj.categoria);

        if (action !== 'unlink') {
            form.append('file', fs.createReadStream(filePath));
        }

        await axios.post(API_SYNC, form, {
            headers: {
                ...form.getHeaders(),
                'Authorization': `Bearer ${apiToken}`
            }
        });

        if (mainWindow && !mainWindow.isDestroyed()) {
            mainWindow.webContents.send('sync-progress', {
                file: path.basename(filePath),
                folder: folderObj.path.split('\\').pop(),
                progress: 100,
                status: 'Concluído'
            });
        }
        if (trayWindow && !trayWindow.isDestroyed()) {
            trayWindow.webContents.send('sync-progress', {
                file: path.basename(filePath),
                folder: folderObj.path.split('\\').pop(),
                progress: 100,
                status: 'Concluído'
            });
        }
    } catch (error) {
        console.error('Erro na sincronização:', error.message);
        if (mainWindow && !mainWindow.isDestroyed()) {
            mainWindow.webContents.send('sync-progress', {
                file: path.basename(filePath),
                folder: folderObj.path.split('\\').pop(),
                progress: 0,
                status: 'Falha no envio'
            });
        }
    }
}

// ----- IPC EVENTS (Interface <=> Main) -----

ipcMain.on('window-minimize', () => mainWindow.minimize());
ipcMain.on('window-close', () => mainWindow.hide()); // Fechar esconde pra bandeja

ipcMain.on('open-dashboard', () => {
    trayWindow.hide();
    mainWindow.show();
});

ipcMain.on('login-request', async (event, credentials) => {
    try {
        const res = await axios.post(API_LOGIN, credentials);
        if (res.data.status === 'success') {
            apiToken = res.data.token;
            user = res.data.user;
            store.set('apiToken', apiToken);
            store.set('user', user);
            
            event.reply('login-response', { success: true });
            mainWindow.webContents.send('auth-status', { isAuth: true, user });
            
            // Inicia monitores
            startAllWatchers();
        }
    } catch (err) {
        let msg = 'Erro de conexão com o servidor.';
        if (err.response && err.response.data && err.response.data.error) {
            msg = err.response.data.error;
        }
        event.reply('login-response', { success: false, error: msg });
    }
});

ipcMain.on('logout-request', () => {
    apiToken = null;
    user = null;
    store.delete('apiToken');
    store.delete('user');
    
    // Desliga radares
    Object.values(watchers).forEach(w => w.close());
    watchers = {};

    mainWindow.webContents.send('auth-status', { isAuth: false });
});

ipcMain.on('request-folders', (event) => {
    event.reply('update-folders-list', folders);
});

ipcMain.on('dialog-add-folder', async (event) => {
    const result = await dialog.showOpenDialog(mainWindow, {
        properties: ['openDirectory']
    });

    if (!result.canceled && result.filePaths.length > 0) {
        const selectedPath = result.filePaths[0];
        // Verifica se já existe
        const exists = folders.find(f => f.path === selectedPath);
        if (!exists) {
            event.reply('folder-selected', selectedPath);
        }
    }
});

ipcMain.on('confirm-add-folder', (event, { path, ano, categoria }) => {
    const exists = folders.find(f => f.path === path);
    if (!exists) {
        const newFolder = { path, ano, categoria };
        folders.push(newFolder);
        store.set('folders', folders);
        
        event.reply('update-folders-list', folders);
        startWatcherForFolder(newFolder);
    }
});

ipcMain.on('remove-folder', (event, folderPath) => {
    folders = folders.filter(f => f.path !== folderPath);
    store.set('folders', folders);
    
    if (watchers[folderPath]) {
        watchers[folderPath].close();
        delete watchers[folderPath];
    }
    event.reply('update-folders-list', folders);
});


// ----- APP LIFECYCLE -----

app.whenReady().then(async () => {
    // Carrega módulo ESM dinamicamente
    Store = (await import('electron-store')).default;
    store = new Store();

    // Inicia variaveis
    apiToken = store.get('apiToken', null);
    user = store.get('user', null);
    folders = store.get('folders', []);

    createWindow();
    await initTray();

    if (apiToken) {
        startAllWatchers();
    }
});

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') app.quit();
});

# Implantação do TopoGest

Este repositório possui dois ambientes independentes no mesmo servidor:

| Branch | Ambiente | Banco | Endereço |
| --- | --- | --- | --- |
| `main` | produção | PostgreSQL | `http://192.168.1.100/topogest/` |
| `development` | homologação | PostgreSQL | `http://catlog.tail738f14.ts.net/topogest/` |

Produção escuta somente no endereço da LAN. Homologação escuta somente no
endereço Tailscale. Cada ambiente usa um contêiner de aplicação e um contêiner
PostgreSQL. Os bancos não publicam portas no host.

## Configuração

Os segredos não pertencem ao Git nem à imagem. No servidor eles ficam em:

- `/opt/topogest/main.env`
- `/opt/topogest/development.env`

Para alterar e-mail, WhatsApp ou outra integração, edite o arquivo do ambiente
correspondente e reinicie seus serviços pelo Portainer. Não envie um arquivo
`.env` ao repositório e não cole segredos nos campos de build da imagem.

## Atualização automática

O serviço `topogest-pipeline` consulta as duas branches. Quando encontra um novo
commit, ele clona uma cópia limpa, constrói imagens versionadas pelo SHA, executa
os testes, aplica as migrations e verifica `/topogest/`. Uma falha antes da
publicação preserva a versão em execução; uma falha no health check restaura as
imagens anteriores.

Os logs e todos os contêineres podem ser consultados no Portainer. Um deploy
manual pode ser solicitado reiniciando `topogest-pipeline`; sem commit novo ele
não recria os ambientes.

## Serviços de cada ambiente

- `topogest-producao` ou `topogest-dev`: Nginx, Laravel/PHP-FPM, fila,
  agendador e Reverb, coordenados pelo Supervisor. É a única porta publicada.
- `topogest-producao-db` ou `topogest-dev-db`: PostgreSQL persistente, sem porta
  publicada.

O Reverb escuta apenas dentro do contêiner na porta 8080. O Nginx encaminha as
conexões WebSocket recebidas em `/topogest/app`, portanto não é necessário abrir
outra porta no servidor.

Os volumes de produção e homologação têm nomes separados. Remover um contêiner
não remove seus dados, mas remover explicitamente os volumes apaga o banco e os
arquivos persistentes daquele ambiente.

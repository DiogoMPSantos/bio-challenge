## BIO RPA

# Setup
cp .env.example .env
php artisan key:generate
composer install
compose update
php artisan migrate (criar um banco chamado bio-challenge na sua base de dados)
php artisan storage:link
php artisan serve

# Selenium
Executar selenium para utilizar as rotas de teste

# Rotas
/table -> Armaneza informaccoes da pagina no banco de dados
/form -> Submit do form
/download -> download do arquivo para usar na rota abaixo
/upload -> upload de arquivo da storage no formulario
/pdf -> exibe csv no browser gerado a partir do pdf mandado

# observacoes

 Os campos (Valor Informado da Guia, Valor Processado da Guia, Valor Liberado da Guia, Valor Glosa da Guia) no rodape de cada pagina
 os campos (Data de realização, Tabela, Código do Procedimento, Descrição, Grau Participação, Valor Informado, Quanti. Executada, Valor Processado, Valor Liberado, Valor Glosa, Código da Glosa) no pdf em formato de tabela não consegui salvar por motivos de estar utilizando regex nao consegui identificar como pegar a lista de informações para salvar no csv. Os campos do rodape nao são carregados ao trazer o content do pdf das paginas e tambem nao foram salvos.

 Optei por nao subir um fix nestes campos devido a demanda de desafios que tenho pendente de atualizar.
Repositório com os dados e scripts utilizados para a realização de uma pesquisa de mestrado sobre a localização dos 
desenvolvedores do core e dos plugins do WordPress. Abaixo segue uma explicação do conteúdo de cada um dos diretórios.

## bicho

Diretório com um dump dos dados extraídos pelo Bicho do Trac utilizado para o core do WordPress. Dados não utilizados na
dissertação mas salvos aqui caso haja interesse em utilizá-los no futuro.

## cvsanaly

Dump das bases de dados do MySQL geradas pelo software CVSAnalY ao extrair dados do repositório do core e do repositório
de plugins do WordPress. Foram essas duas bases de dados que foram utilizadas para calcular o número de commits feito
por cada um dos desenvolvedores em cada um dos dois repositórios analisados. Esses dados estão consolidados nas
planilhas disponíveis no diretório "tabelas".

## get_location

Esse diretório contém o script utilizado para primeiro extrair o campo localização do perfil dos desenvolvedores no
profiles.wordpress.org e depois buscar na API do OSM qual o país correspondente ao que está no campo já que este campo é
um campo aberto e os desenvolvedores podem escrever qualquer coisa.

Para utilizá-lo é necessário primeiro instalar as dependências utilizando o Composer (todos os comandos exibidos devem
ser executados a partir do próprio diretório e não da raiz do repositório):

composer install

Então para pegar a informação presente no campo localização no perfil dos desenvolvedores do WP:

php get_location.php --get-location

Esse comando irá buscar um arquivo chamado users com uma lista de usuários (um usuário por linha). Para um exemplo, veja
o arquivo users_example. Para cada usuário presente no arquivo users, um request será feito à página dele no site
profiles.wordpress.org em busca do campo "Location". A informação encontrada para cada usuário será salva no arquivo
users_data.csv.

Uma vez criado o arquivo users_data.csv, é possível chamar novamente o get_location.php com outro parâmetro para a
partir do campo "Location" tentar descobrir o nome do país usando a API do OpenStreetMap:

php get_location.php --get-country

Esse último comando irá adicionar ao users_data.csv o país de cada usuário para aqueles em que foi possível determinar
o país.

## tabelas 

Esse diretório contém duas tabelas do LibreOffice Calc. Uma chamada core.ods possuí todos os dados que foram compilados 
em relação aos desenvolvedores do core do WP. A outra, plugins.ods, possuí os mesmos dados mas para os desenvolvedores
dos plugins do WP.

Ambas estão organizadas em várias abas e apresentam uma lista com o universo de desenvolvedores considerados (lista
extraída a partir do repositório de código do software), o número de commits realizado por cada um dos desenvolvedores
(seja no core ou no repositório de plugins), o país de residência do desenvolvedor (para aqueles que foi possível 
determinar essa informação) e o idioma falado naquele país. Também nesses arquivos estão presentes os gráficos que foram
gerados para a dissertação com base nas informações coletadas.

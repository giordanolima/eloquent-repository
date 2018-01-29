# Eloquent Repository
[![Latest Stable Version](https://poser.pugx.org/giordanolima/eloquent-repository/v/stable)](https://packagist.org/packages/giordanolima/eloquent-repository) 
[![Total Downloads](https://poser.pugx.org/giordanolima/eloquent-repository/downloads)](https://packagist.org/packages/giordanolima/eloquent-repository) 
[![License](https://poser.pugx.org/giordanolima/eloquent-repository/license)](https://packagist.org/packages/giordanolima/eloquent-repository)
[![StyleCI](https://styleci.io/repos/82729156/shield?branch=master)](https://styleci.io/repos/82729156)

[Documentação em português.](README_PT.md)
[Documentação em inglês.](README.md)

A ideia da criação desse pacote surgiu com a necessidade de criar uma camada extra nas minhas aplicações para cachear as consultas dos bancos de dados.
Antes disso, usava o pacote do [l5-repository](https://github.com/andersao/l5-repository) e o driver de cache disponível no pacote. Porém achava o pacote um pouco complexo quando precisa criar algumas consultas um pouco mais complexas e bastante burocrático para criar consultas simples. Além disso não estava totalmente satisfeito com o driver de cache Embora o pacote consiga cachear boa parte das consultas, eu não consegui reduzir totalmente as consultas, principalmente pela restrição de não conseguir cachear os relacionamentos. Num primeiro momento pensei em criar pull requests pra sugerir essas alterações, porém o pacote não parecia ser muito ativo, fazendo que a implementação demorasse pra ser aplicada, além de necessitar alterar boa parte da ideia e da estrutura atual (de um pacote que não era meu, então achei que sugerir tamanha alteração poderia ser invasivo).

Com isso, comecei a dar início no desenvolvimento do pacote. Sempre com o foco voltado para facilitar a realização das consultas, bem como aprimorar o driver de cache (só me sentiria satisfeito caso conseguisse reduzir a *zero* o número de consultas no banco).
Em seguida surgiu então a [primeira versão do pacote](https://github.com/giordanolima/eloquent-repository/tree/1.0). Ele surgiu flexível e com meu driver de cache bastante adiantado. Porém ao começar a divulgar meu pacote, ouvi bastante críticas em relação ao fato de pacote ser "excessivamente permissivo", principalmente em relação a disponibilidade de forma muito transparente os métodos do eloquent (na primeira versão eles eram todos públicos, podendo ser acessados inclusive através dos controllers).

Então ouvi todas as críticas, observei bastante o que a comunidade tinha pra me falar à respeito (principalmente no que diz respeito ao conceito de repositórios) e passei a desenvolver essa segunda versão do pacote. Encapsulei os métodos do Eloquente/QueryBuider como protegidos (para evitar o acesso deles diretamente de forma pública) e realizei alguns ajustes de estrutura do pacote.

Agora ele está aí disponível pra uso e, principalmente, disponível para críticas e sugestões de melhorias do pacote. Estou muito longe de ter total conhecimento principalmente no que diz respeito aos padrões de projeto e quero ouvir tudo o que vocês programadores tem a dizer naquilo que pde ser melhorado no pacote.

Espero que gostem, usem e enviem pull requests.
Um abraço!

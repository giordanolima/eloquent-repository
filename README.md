# Eloquent Repository
[![Latest Stable Version](https://poser.pugx.org/giordanolima/eloquent-repository/v/stable)](https://packagist.org/packages/giordanolima/eloquent-repository) 
[![Total Downloads](https://poser.pugx.org/giordanolima/eloquent-repository/downloads)](https://packagist.org/packages/giordanolima/eloquent-repository) 
[![License](https://poser.pugx.org/giordanolima/eloquent-repository/license)](https://packagist.org/packages/giordanolima/eloquent-repository)
[![StyleCI](https://styleci.io/repos/82729156/shield?branch=master)](https://styleci.io/repos/82729156)

Pacote para auxiliar na implementação do Repository Pattern utilizando o Eloquent ORM.
Tem como principal característica a flexibilidade e naturalidade de uso e um poderoso driver para cache de consultas.
## Instalação
Instalação via Composer
```bash
composer require giordanolima/eloquent-repository
```
Para configurar as opções do pacote, declare o Service Provider no arquivo `config/app.php`.
```php
'providers' => [
    ...
    GiordanoLima\EloquentRepository\RepositoryServiceProvider::class,
],
```
Para publicar o arquivo de configuração:
```shell
php artisan vendor:publish
```
### Uso
Para começar a usar é necessário criar sua classe de repositório e extender o `BaseRepository` disponível do pacote. Além disso, também é necessário indicar o *Model* que será usado para realizar as consultas.
Exemplo:
```php
namespace App\Repositories;
use GiordanoLima\EloquentRepository\BaseRepository;
class UserRepository extends BaseRepository
{
	protected function model() {
        return \App\User::class;
    }
}
```
Com essa classe é possível realizar consultas da mesma maneira que é usado no Elquent.
```php
namespace App\Repositories;
use GiordanoLima\EloquentRepository\BaseRepository;
class UserRepository extends BaseRepository
{
	protected function model() {
        return \App\User::class;
    }
    
    public function getAllUser(){
        return $this->all();
    }
    
    public function getByName($name) {
        return $this->where("name", $name)->get();
    }
    
    // É possível criar métodos com consultas parciais
    public function filterByProfile($profile) {
        return $this->where("profile", $profile);
    }
    
    // Depois é possível usar as consultas parciais dentro do próprio repositório
    public function getAdmins() {
        return $this->filterByProfile("admin")->get();
    }
    public function getEditors() {
        return $this->filterByProfile("editor")->get();
    }
    
    // Também é possível usar Eager Loading nas consultas
    public function getWithPosts() {
        return $this->with("posts")->get();
    }
}
```
Para usar a classe, basta injetá-las nos controllers.
```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
class UserController extends Controller
{
	protected function index(UserRepository $repository) {
        return $repository->getAdmins();
    }
}
```
A injeção também pode ser feita no construtor pra utilizar o repositório em todos os métodos.
```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
class UserController extends Controller
{
    private $repository;
	public function __construct()(UserRepository $repository) {
        $this->repository = $repository;
    }
    
    public function index() {
        return $this->repository->all();
    }
    
    public function edit($id) {
        // É possível também utilizar os métodos do Model diretamente no controller.
        return $this->repository->findOrFail($id);
    }
    
}
```
#### Paginate
Como valor padrão, sempre que o método `paginate` é usando, serão listados *15* registros por página. Este valor padrão pode ser definido no arquivo de configuração para ser usado em todos os repositórios.
Caso seja necessário, é possível alterar o valor padrão para um único repositório, basta sobreescrever a propriedade `perPage` com o valor desejado.
```php
namespace App\Repositories;
use GiordanoLima\EloquentRepository\BaseRepository;
class UserRepository extends BaseRepository
{
    // Deste modo, sempre que o método paginate for usado neste repositório
    // serão exibidos somente 10 registros por página.
    protected $perPage = 10;
	protected function model() {
        return \App\User::class;
    }
}
```
#### OrderBy
É possível declarar um campo e uma diração padrão para ser usado em todas as consultas de um determinado repositório.
Ainda assim é possível escolher outras formas de ordenação, bem como é possível usar nenhum tipo de ordenação.
```php
namespace App\Repositories;
use GiordanoLima\EloquentRepository\BaseRepository;
class UserRepository extends BaseRepository
{
    protected $orderBy = "created_at";
    protected $orderByDirection = "DESC";
	protected function model() {
        return \App\User::class;
    }
    
    public function getAllUser(){
        // Neste consulta será usada a ordenação padrão do repositório.
        return $this->all();
    }
    
    public function getByName($name) {
        // Neste consulta será usado somente a ordenação declarada.
        return $this->orderBy("name")->where("name", $name)->get();
    }
    
    // É possível criar métodos com consultas parciais
    public function getWithoutOrder() {
        // Neste consulta não será usada nenhuma ordenação.
        return $this->skipOrderBy()->all();
    }
    
}
```
#### GlobalScope
É possível determinar um escopo para ser usado em todas as consultas utilizadas no repositório.
Caso seja necessário, também é possível ignorar esse escopo global.
```php
namespace App\Repositories;
use GiordanoLima\EloquentRepository\BaseRepository;
class AdminRepository extends BaseRepository
{
    protected function model() {
        return \App\User::class;
    }
    protected function globalScope() {
        return $this->where('is_admin', true);
    }
    
    public function getAdmins() {
        // Neste consulta será incluído o escopo global declarado.
        return $this->all();
    }
    
    public function getAll() {
        Neste consulta não será incluído o escopo global declarado.
        return $this->skipGlobalScope()->all();
    }
}
```
### Cache
O pacote acompanha um poderoso driver para cache. A ideia é que uma vez realizada a consulta, esta seja armazenado em cache. Após o cache ser feito, é possível reduzir a zero número de acessos ao banco de dados.
Para utilizar o driver, basta extender a trait que implementa.
```php
namespace App\Repositories;
use GiordanoLima\EloquentRepository\BaseRepository;
use GiordanoLima\EloquentRepository\CacheableRepository;
class UserRepository extends BaseRepository
{
    use CacheableRepository;
	protected function model() {
        return \App\User::class;
    }
}
```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/*
|##########################################################################
| # Model Category — Representa a tabela "categories"                     #
|##########################################################################
|
|##########################################################################
| # TEORIA: Relacionamentos 1:N (One-to-Many) — Relações no Eloquent      #
|##########################################################################
|
| CARDINALIDADE: Uma categoria TEM MUITAS tarefas.
|                Muitas tarefas PERTENCEM a UMA categoria.
|
|    categories (1) ────────── (N) tasks
|
| Para isso, a tabela 'tasks' tem uma coluna FOREIGN KEY: category_id
| que aponta para categories.id.
|
| NO ELOQUENT, declaramos relações como MÉTODOS PÚBLICOS:
|   • Lado "tem muitos":   public function tasks() { return $this->hasMany(Task::class); }
|   • Lado "pertence a":   public function category() { return $this->belongsTo(Category::class); }
|
| COMO USAR:
|   $category = Category::find(1);
|   $category->tasks;      // "Dynamic Property" → executa query e devolve Collection de Tasks
|   $category->tasks();    // Retorna o Builder (ainda não rodou a query) → útil para where():
|                          //   $category->tasks()->where('status','pendente')->get()
|
| TIPOS DE RELACIONAMENTOS:
|   • hasOne()         → 1:1 (usuário tem UM perfil)
|   • hasMany()        → 1:N (categoria tem N tarefas)
|   • belongsTo()      → inverso de hasOne/hasMany
|   • belongsToMany()  → N:M (usuários tem muitas roles via tabela pivot)
|##########################################################################
*/
class Category extends Model
{
    use HasFactory;

    /**
     * Campos permitidos em Mass Assignment (atribuição em massa).
     * Sem isso, Category::create([...]) daria erro de segurança.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
    ];

    /**
     * ⚙️ Conversão automática de tipos (Attribute Casting).
     * Garante que created_at / updated_at são objetos Carbon (não strings).
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'name' => 'string' etc. (string padrão não precisa declarar)
    ];

    /*
    |--------------------------------------------------------------------------
    | MUTATORS / ACCESSORS (Modificam como atributos são lidos/gravados)
    |--------------------------------------------------------------------------
    |
    | ########################################################################
    | # TEORIA: Mutators e Accessors do Eloquent                             #
    | ########################################################################
    |   Accessor: define um atributo "virtual" ao LER do banco.
    |     Ex: public function getNameWithSlugAttribute()
    |               { return $this->name . ' (' . $this->slug . ')'; }
    |     Uso: $category->name_with_slug   // "Trabalho (trabalho)"
    |
    |   Mutator: transforma o valor ANTES de salvar no banco.
    |     Ex: public function setNameAttribute($val)
    |               { $this->attributes['name'] = ucwords($val); }
    |
    | Abaixo usamos um booted hook para GERAR o slug automaticamente
    | ao criar uma categoria (sem precisar enviar no request).
    */
    protected static function booted()
    {
        // Hook: quando ESTIVERMOS CRIANDO (creating) uma categoria...
        static::creating(function (Category $category) {
            /*
             * 🎓 TEORIA: Slug = versão URL-amigável.
             *   "Minha Categoria Legal" → "minha-categoria-legal"
             * Usamos Str::slug que substitui espaços por -, remove acentos,
             * coloca em lowercase.
             * Isso é usado em URLs amigáveis (ex: /categories/minha-categoria-legal).
             */
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELAÇÕES (Relacionamentos do Model)
    |--------------------------------------------------------------------------
    */

    /**
     * 🧑 Usuário DONO desta categoria (Categoria → User).
     *
     * INVERSO da relação User::categories() (hasMany).
     * Retorna: um único Model User (BelongsTo).
     *
     * Uso: $category->user; // object User dono
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 📝 Tarefas pertencentes a esta categoria (Categoria → Tasks).
     *
     * Relação 1:N: UMA categoria → MUITAS tarefas.
     * Retorna: Query Builder de HasMany. Acesso como propriedade dá Collection.
     *
     * Uso:
     *   $categoria = Category::with('tasks')->find(1); // Eager Load
     *   $categoria->tasks;  // Collection de Task[]
     *
     * 🔐 Escopo por usuário: note que NÃO temos where('user_id', ...) aqui
     * porque a categoria JÁ pertence ao usuário; as tasks compartilham o
     * mesmo user_id por consistência, mas na prática podemos filtrar
     * no Repository/Service.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}

# Migration CodeIgniter 3 vers CodeIgniter 4 (CoolHome)

## Etat actuel

- Le socle CodeIgniter 4 est initialise et fonctionnel.
- L'entree principale passe par CI4.
- Un fallback legacy reste disponible via `/legacy.php`.
- Le code metier CI3 (`application/*`) n'est pas encore porte en natif CI4.
- Le flux d'authentification principal (`/login`) est migre en CI4.
- Le flux `/homes` est migre en CI4 (version simplifiee).
- Les flux `/home/index/{id}` et `/room/index/{id}` sont migres en CI4 (version simplifiee).
- Les flux `/devices` et `/device/index/{id}` sont migres en CI4.

## Ce qui a ete migre

- Dependances Composer vers `codeigniter4/framework`.
- Structure CI4 de base (`app/Config`, `public/index.php`, `spark`).
- Configuration applicative initiale (URL, locale, timezone).
- Route de transition:
  - `/` redirige vers le legacy
  - `/legacy` redirige vers `/legacy.php`
  - `/migration` affiche l'etat de migration CI4
- Flux Login CI4:
  - `/login`
  - `/login/create`
  - `/login/recover`
  - `/login/setPassword/{email}/{key}`
  - `/login/out`
- Flux Homes CI4:
  - `/homes`
  - `/homes/remove/{id}`
- Flux Home/Room CI4:
  - `/home/index/{id}`
  - `/home/addroom/{id}`
  - `/room/index/{id}`
- Flux Device(s) CI4:
  - `/devices`
  - `/devices/firmwares`
  - `/devices/updates`
  - `/devices/dl/{platform}/{file}`
  - `/device/index/{id}`
  - `/device/datasfordt/{id}`
  - `/device/datachart/{id}`

## Strategy de portage recommandee

### 1) Controllers

Migrer chaque controleur de `application/controllers` vers `app/Controllers`:

- Ajouter `namespace App\Controllers;`
- Etendre `BaseController` (ou un equivalent custom CI4)
- Remplacer:
  - `$this->input->post()` par `$this->request->getPost()`
  - `$this->uri->segment(n)` par `$this->request->uri->getSegment(n)`
  - `redirect('/x')` legacy par `return redirect()->to('/x')`

### 2) Models

Deux options:

- Portage progressif vers `CodeIgniter\Model`
- Ou conservation des classes metier legacy dans un namespace dedie, puis adaptation des acces DB via `Config\Database::connect()`

### 3) Session/Auth

- Remplacer les acces `CI_Controller::get_instance()->session`.
- Utiliser `session()` et services CI4.

### 4) Vues

- Deplacer les vues de `application/views` vers `app/Views`.
- Adapter les inclusions a `return view('path/to/view', $data);`.

### 5) Config

- Deplacer la config legacy de `application/config/*` vers:
  - `.env`
  - `app/Config/*`
- Eviter les chemins absolus hardcodes.

### 6) Routing

- Transformer `application/config/routes.php` vers `app/Config/Routes.php`.
- Declarer explicitement les routes critiques (`homes`, `home/index`, `api/*`, etc.).

## Ordre de migration conseille pour CoolHome

1. Controller `Api` avec tests de non-regression.

## Verifications minimales apres chaque lot

- `php spark routes`
- `php spark serve`
- Test manuel login + navigation maison + ajout piece + actions device
- Verification logs dans `writable/logs`

## Notes techniques

- Le fallback CI3 est volontairement conserve pour permettre une migration sans interruption.
- Une fois tous les controleurs/routes portes, supprimer:
  - `public/legacy.php`
  - `legacy-index.php`
  - le dossier `application/`
  - le dossier `system/` legacy

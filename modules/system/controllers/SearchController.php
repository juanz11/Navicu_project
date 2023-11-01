namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        // Procesar la búsqueda aquí

        // Redirigir al usuario a la URL real de búsqueda
        return redirect('/busqueda/' . $page . '/' . $q);
    }
}
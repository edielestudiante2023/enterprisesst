public function index($id)
{
    // ...existing code para armar $data...
    var_dump($data); exit; // <-- Esto mostrará en pantalla todo lo que se envía a la vista
    return view('client/dashboard', $data);
}

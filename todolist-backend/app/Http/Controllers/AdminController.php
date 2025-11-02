<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function getAllUsers(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'role', 'last_login_at', 'created_at')->get();

        return response()->json([
            'total' => $users->count(),
            'users' => $users
        ]);
    }

    public function getStats(Request $request)
    {
        $totalUsers = User::count();
        $onlineUsers = User::where('last_login_at', '>=', now()->subMinutes(5))->count(); // online nos últimos 5 minutos
        $admins = User::where('role', 'admin')->count();

        return response()->json([
            'total_users' => $totalUsers,
            'online_users' => $onlineUsers,
            'admins' => $admins
        ]);
    }

    public function deleteUser($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'Usuário não encontrado'], 404);
    }

    // Se quiser, pode impedir que admins sejam excluídos
    // if ($user->role === 'admin') {
    //     return response()->json(['message' => 'Não é permitido excluir administradores'], 403);
    // }

    $user->delete();

    return response()->json(['message' => 'Usuário excluído com sucesso']);
}

}

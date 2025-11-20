<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use Illuminate\Http\Request;


/**
 * @OA\Info(
 *     title="ToDo API",
 *     version="1.0.0",
 *     description="Dokumentation der ToDo-API"
 * )
 */
class TodoController extends Controller
{
    /**
     * Zeigt alle Aufgaben des angemeldeten Benutzers an.
     *
     * @OA\Get(
     *     path="/api/todos",
     *     summary="Gibt eine Liste aller ToDos zurück",
     *     description="Liefert alle ToDos des aktuell eingeloggten Users als JSON-Antwort",
     *     tags={"ToDos"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste der ToDos",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="titel", type="string", example="Wäsche waschen"),
     *                 @OA\Property(property="erledigt", type="boolean", example=false),
     *                 @OA\Property(property="faellig_am", type="string", format="date", example="2025-05-01"),
     *                 @OA\Property(property="erstellt_am", type="string", format="date-time", example="2025-04-01T10:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Alle Todos des Users mit ID 1 holen
        $query = Todo::where('user_id', auth()->id());

        if ($request->has('faellig')) {
            $wert = $request->query('faellig');

            if ($wert === 'heute') {
                $query->whereDate('faellig_am', today());
            } elseif ($wert === 'ueberfaellig') {
                $query->whereDate('faellig_am', '<', today());
            } elseif ($wert === 'kommend') {
                $query->whereDate('faellig_am', '>', today());
            }
        }
        $todos = $query->get();
        return TodoResource::collection($todos);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Zuerst validieren wir die eingehenden Daten
        $validated = $request->validate([
            'titel' => 'required|string|max:255',
            'faellig_am' => 'nullable|date',
        ]);

        // Dann erstellen wir den neuen Datensatz mit den geprüften Werten
        $todo = Todo::create([
            'user_id' => auth()->id(), // Fester Benutzer (bis später Auth kommt)
            'titel' => $validated['titel'],
            'faellig_am' => $validated['faellig_am'] ?? null,
            'erledigt' => false, // Standardwert bei neuen Aufgaben
        ]);

        // Zum Schluss geben wir eine klare JSON-Antwort zurück
        return response()->json([
            'erfolg' => true,
            'nachricht' => 'Aufgabe wurde erfolgreich erstellt.',
            'daten' => new TodoResource($todo),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Einzelnes Todo des Users mit ID 1 holen
        $todo = Todo::where('user_id', auth()->id())->findOrFail($id);;
        return new TodoResource($todo);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'titel' => 'required|string|max:255',
            'faellig_am' => 'nullable|date',
            'erledigt' => 'required|boolean',
        ]);

        $todo = Todo::where('user_id', auth()->id())->findOrFail($id);
        $todo->update($validated);

        return response()->json([
            'erfolg' => true,
            'nachricht' => 'Aufgabe wurde erfolgreich aktualisiert.',
            'daten' => new TodoResource($todo)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $todo = Todo::where('user_id', auth()->id())->findOrFail($id);
        $todo->delete();

        return response()->json([
            'erfolg' => true,
            'nachricht' => 'Aufgabe wurde gelöscht.'
        ]);
    }

    /**
     * Patch the specified resource from storage.
     */
    public function toggle($id)
    {
        // Finde die Aufgabe, die dem aktuell eingeloggten Benutzer gehört
        $todo = Todo::where('user_id', auth()->id())->findOrFail($id);

        // erledigt-Wert umkehren (true → false oder false → true)
        $todo->erledigt = !$todo->erledigt;
        $todo->save();

        return response()->json([
            'erfolg' => true,
            'nachricht' => 'Aufgabe wurde aktualisiert.',
            'daten' => new TodoResource($todo)
        ]);
    }
}

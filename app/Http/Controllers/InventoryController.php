<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Exception;

class InventoryController extends Controller
{
    private const CATEGORIES = [
        'Books & Learning Materials',
        'Cleaning & Sanitation',
        'Electronics & ICT',
        'Food & Kitchen Supplies',
        'Furniture & Equipment',
        'Maintenance & Tools',
        'Medical & First Aid',
        'Sports & Recreation',
        'Stationery & Teaching Supplies',
        'Uniforms',
        'Other',
    ];

    /**
     * Display a listing of inventory items.
     */
    public function index(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($items) use ($search) {
                $items->where('item_name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->get('status') === 'low') {
            $query->whereColumn('quantity', '<=', 'alert_level');
        } elseif ($request->get('status') === 'available') {
            $query->whereColumn('quantity', '>', 'alert_level');
        }

        $items = $query->orderBy('category')->orderBy('item_name')->paginate(15)->withQueryString();
        $summary = [
            'item_types' => InventoryItem::count(),
            'units' => (int) InventoryItem::sum('quantity'),
            'low_stock' => InventoryItem::whereColumn('quantity', '<=', 'alert_level')->count(),
            'stock_value' => (float) InventoryItem::selectRaw('COALESCE(SUM(quantity * unit_price), 0) as total')->value('total'),
        ];
        $categoryCounts = InventoryItem::selectRaw('category, COUNT(*) as item_count')
            ->groupBy('category')->pluck('item_count', 'category');
        $categories = $this->categoryOptions();

        return view('inventory.index', compact('items', 'summary', 'categories', 'categoryCounts'));
    }

    public function create()
    {
        // The catalogue page contains the item registration form.
        return redirect()->route('inventory.index');
    }

    /**
     * Store a newly created item in the database.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'item_name'   => 'required|string|max:255',
            'sku'         => 'nullable|string|unique:inventory_items,sku',
            'category'    => ['required', 'string', Rule::in($this->categoryOptions())],
            'alert_level' => 'required|integer|min:0',
            'unit_price'  => 'required|numeric|min:0',
            'opening_quantity' => 'nullable|integer|min:0',
        ]);

        // Auto-generate SKU if left empty
        if (empty($data['sku'])) {
            do {
                $data['sku'] = 'SKU-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['item_name']), 0, 3)) . '-' . strtoupper(bin2hex(random_bytes(3)));
            } while (InventoryItem::where('sku', $data['sku'])->exists());
        }

        $openingQuantity = (int) ($data['opening_quantity'] ?? 0);
        unset($data['opening_quantity']);
        $data['quantity'] = $openingQuantity;

        DB::transaction(function () use ($data, $openingQuantity) {
            $item = InventoryItem::create($data);
            if ($openingQuantity > 0) {
                InventoryStock::create([
                    'inventory_item_id' => $item->id,
                    'type' => 'in',
                    'quantity' => $openingQuantity,
                    'person_involved' => Auth::user()?->name,
                    'remarks' => 'Opening stock balance',
                ]);
            }
        });

        return back()->with('success', 'New item registered successfully.');
    }

    /**
     * Handle Stock In / Stock Out movements with improved error handling.
     */
    public function updateStock(Request $request)
    {
        $request->validate([
            'item_id'  => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
            'type'     => 'required|in:in,out',
            'remarks'  => 'nullable|string|max:255',
            'person_involved' => 'nullable|string|max:255'
        ]);

        try {
            $result = DB::transaction(function () use ($request) {
                $item = InventoryItem::whereKey($request->item_id)->lockForUpdate()->firstOrFail();

                if ($request->type === 'out' && $item->quantity < (int) $request->quantity) {
                    return ['insufficient' => true, 'quantity' => $item->quantity];
                }

                $item->quantity += $request->type === 'in' ? (int) $request->quantity : -(int) $request->quantity;
                $item->save();

                InventoryStock::create([
                    'inventory_item_id' => $item->id,
                    'type'              => $request->type,
                    'quantity'          => $request->quantity,
                    'remarks'           => $request->remarks,
                    'person_involved'   => $request->person_involved ?: Auth::user()?->name,
                ]);

                return ['insufficient' => false, 'quantity' => $item->quantity];
            });

            if ($result['insufficient']) {
                return back()->with('error', 'Not enough stock. Available quantity: ' . $result['quantity']);
            }

            return back()->with('success', 'Stock updated successfully.');

        } catch (Exception $e) {
            Log::error("Inventory Update Error: " . $e->getMessage());
            return back()->with('error', 'Something went wrong while updating stock.');
        }
    }

    /**
     * Remove the specified item from inventory.
     */
    public function destroy($id)
    {
        $item = InventoryItem::findOrFail($id);

        if ($item->quantity > 0 || $item->logs()->exists()) {
            return back()->with('error', 'This item has stock or movement history. Keep it for accurate inventory records.');
        }

        $item->delete();
        return back()->with('success', 'Item removed from inventory.');
    }

    /**
     * Display the audit trail of all stock movements.
     */
    public function logs(Request $request)
    {
        $request->validate(['type' => 'nullable|in:in,out', 'search' => 'nullable|string|max:100']);
        $query = InventoryStock::with('item')->latest();
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($logs) use ($search) {
                $logs->where('remarks', 'like', "%{$search}%")
                    ->orWhere('person_involved', 'like', "%{$search}%")
                    ->orWhereHas('item', fn ($item) => $item->where('item_name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%"));
            });
        }
        $logs = $query->paginate(25)->withQueryString();
        return view('inventory.logs', compact('logs'));
    }

    public function edit($id)
    {
        $item = InventoryItem::findOrFail($id);
        $categories = $this->categoryOptions($item->category);
        return view('inventory.edit', compact('item', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);
        $data = $request->validate([
            'item_name' => 'required|string|max:255',
            'sku' => ['required', 'string', 'max:100', Rule::unique('inventory_items', 'sku')->ignore($item->id)],
            'category' => ['required', 'string', Rule::in($this->categoryOptions($item->category))],
            'alert_level' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $item->update($data);
        return redirect()->route('inventory.index')->with('success', 'Inventory item details updated.');
    }
    public function lowStockAlerts()
    {
        // Fetch items where current quantity is less than or equal to alert_level
        $lowStockItems = InventoryItem::whereColumn('quantity', '<=', 'alert_level')
                                    ->orderBy('quantity', 'asc')
                                    ->paginate(25);

        return view('inventory.low_stock', compact('lowStockItems'));
    }

    private function categoryOptions(?string $existingCategory = null): array
    {
        return collect(self::CATEGORIES)
            ->merge(InventoryItem::query()->whereNotNull('category')->distinct()->pluck('category'))
            ->push($existingCategory)
            ->filter()
            ->map(fn ($category) => trim($category))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}

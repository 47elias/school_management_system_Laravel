<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class InventoryController extends Controller
{
    /**
     * Display a listing of inventory items.
     */
    public function index()
    {
        $items = InventoryItem::orderBy('item_name', 'asc')->get();
        // Pass logs to index if you want a summary on the main page
        return view('inventory.index', compact('items'));
    }

    /**
     * Store a newly created item in the database.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'item_name'   => 'required|string|max:255',
            'sku'         => 'nullable|string|unique:inventory_items,sku',
            'category'    => 'nullable|string|max:100',
            'alert_level' => 'required|integer|min:0',
            'unit_price'  => 'required|numeric|min:0',
        ]);

        // Auto-generate SKU if left empty
        if (empty($data['sku'])) {
            $data['sku'] = 'SKU-' . strtoupper(substr($data['item_name'], 0, 3)) . '-' . rand(1000, 9999);
        }

        InventoryItem::create($data);

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

        $item = InventoryItem::findOrFail($request->item_id);

        if ($request->type == 'out' && $item->quantity < $request->quantity) {
            return back()->with('error', 'Insufficient stock! Current balance: ' . $item->quantity);
        }

        try {
            DB::transaction(function () use ($request, $item) {
                // 1. Update the Item Quantity
                if ($request->type == 'in') {
                    $item->increment('quantity', $request->quantity);
                } else {
                    $item->decrement('quantity', $request->quantity);
                }

                // 2. Create the Stock Log
                InventoryStock::create([
                    'inventory_item_id' => $item->id,
                    'type'              => $request->type,
                    'quantity'          => $request->quantity,
                    'remarks'           => $request->remarks,
                    'person_involved'   => $request->person_involved ?? auth::user()->name
                ]);
            });

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

        // Prevent deletion if stock is not zero (Optional safety check)
        if ($item->quantity > 0) {
            return back()->with('error', 'Cannot delete item with remaining stock. Clear stock first.');
        }

        $item->delete();
        return back()->with('success', 'Item removed from inventory.');
    }

    /**
     * Display the audit trail of all stock movements.
     */
    public function logs()
    {
        // Added pagination and eager loading of the item relationship
        $logs = InventoryStock::with('item')->latest()->paginate(20);
        return view('inventory.logs', compact('logs'));
    }
    public function lowStockAlerts()
    {
        // Fetch items where current quantity is less than or equal to alert_level
        $lowStockItems = InventoryItem::whereColumn('quantity', '<=', 'alert_level')
                                    ->orderBy('quantity', 'asc')
                                    ->get();

        return view('inventory.low_stock', compact('lowStockItems'));
    }
}

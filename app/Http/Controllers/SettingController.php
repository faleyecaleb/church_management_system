<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:settings.view')->only(['index', 'show']);
        $this->middleware('permission:settings.update')->only(['edit', 'update', 'updateBatch']);
    }

    public function index(Request $request)
    {
        $query = Setting::query();

        if ($request->filled('group')) {
            $query->where('group', $request->input('group'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $settings = $query->orderBy('group')->orderBy('key')->paginate(20);
        $groups = Setting::getAvailableGroups();

        return view('settings.index', compact('settings', 'groups'));
    }

    public function show(Setting $setting)
    {
        return view('settings.show', compact('setting'));
    }

    public function edit(Setting $setting)
    {
        $types = Setting::getAvailableTypes();
        return view('settings.edit', compact('setting', 'types'));
    }

    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'value' => $this->getValidationRules($setting),
            'description' => 'nullable|string|max:1000'
        ]);

        if (!$setting->validateValue($validated['value'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['value' => 'The provided value is not valid for this setting type.']);
        }

        $setting->update([
            'value' => $validated['value'],
            'description' => $validated['description']
        ]);

        // Clear cache if this is an autoloaded setting
        if ($setting->autoload) {
            Cache::forget(Setting::CACHE_KEY);
        }

        return redirect()->route('settings.show', $setting)
            ->with('success', 'Setting updated successfully.');
    }

    public function updateBatch(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required'
        ]);

        $errors = [];
        $updated = 0;

        foreach ($validated['settings'] as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if (!$setting) {
                $errors[] = "Setting '{$key}' not found.";
                continue;
            }

            $validator = Validator::make(
                ['value' => $value],
                ['value' => $this->getValidationRules($setting)]
            );

            if ($validator->fails()) {
                $errors[] = "Invalid value for setting '{$key}': " . 
                    $validator->errors()->first('value');
                continue;
            }

            if (!$setting->validateValue($value)) {
                $errors[] = "The provided value for '{$key}' is not valid for its type.";
                continue;
            }

            $setting->update(['value' => $value]);
            $updated++;
        }

        // Clear cache if any autoloaded settings were updated
        if (Setting::where('autoload', true)
            ->whereIn('key', array_keys($validated['settings']))
            ->exists()) {
            Cache::forget(Setting::CACHE_KEY);
        }

        $message = $updated . ' settings updated successfully.';
        if (!empty($errors)) {
            $message .= ' Errors: ' . implode(' ', $errors);
            $type = 'warning';
        } else {
            $type = 'success';
        }

        return redirect()->route('settings.index')
            ->with($type, $message);
    }

    protected function getValidationRules(Setting $setting)
    {
        $rules = ['required'];

        switch ($setting->type) {
            case 'boolean':
                $rules[] = 'boolean';
                break;

            case 'integer':
                $rules[] = 'integer';
                if (isset($setting->options['min'])) {
                    $rules[] = 'min:' . $setting->options['min'];
                }
                if (isset($setting->options['max'])) {
                    $rules[] = 'max:' . $setting->options['max'];
                }
                break;

            case 'float':
                $rules[] = 'numeric';
                if (isset($setting->options['min'])) {
                    $rules[] = 'min:' . $setting->options['min'];
                }
                if (isset($setting->options['max'])) {
                    $rules[] = 'max:' . $setting->options['max'];
                }
                break;

            case 'string':
                $rules[] = 'string';
                if (isset($setting->options['max_length'])) {
                    $rules[] = 'max:' . $setting->options['max_length'];
                }
                if (isset($setting->options['pattern'])) {
                    $rules[] = 'regex:' . $setting->options['pattern'];
                }
                break;

            case 'email':
                $rules[] = 'email';
                break;

            case 'url':
                $rules[] = 'url';
                break;

            case 'array':
                $rules[] = 'array';
                if (isset($setting->options['max_items'])) {
                    $rules[] = 'max:' . $setting->options['max_items'];
                }
                break;

            case 'enum':
                if (!empty($setting->options['allowed_values'])) {
                    $rules[] = 'in:' . implode(',', $setting->options['allowed_values']);
                }
                break;

            case 'json':
                $rules[] = 'json';
                break;
        }

        return implode('|', $rules);
    }

    public function export()
    {
        $settings = Setting::all(['key', 'value', 'type', 'group', 'description', 'autoload', 'options']);
        $filename = 'settings_' . now()->format('Y-m-d_His') . '.json';

        return response()->json($settings)
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json|max:2048'
        ]);

        try {
            $content = json_decode(file_get_contents($request->file('file')), true);

            if (!is_array($content)) {
                throw new \Exception('Invalid file format');
            }

            $imported = 0;
            $errors = [];

            foreach ($content as $settingData) {
                try {
                    $setting = Setting::where('key', $settingData['key'])->first();

                    if ($setting) {
                        // Update existing setting
                        if ($setting->type !== $settingData['type']) {
                            $errors[] = "Type mismatch for setting '{$settingData['key']}'";
                            continue;
                        }

                        $setting->update($settingData);
                    } else {
                        // Create new setting
                        Setting::create($settingData);
                    }

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Failed to import setting '{$settingData['key']}': " . 
                        $e->getMessage();
                }
            }

            // Clear cache as settings might have changed
            Cache::forget(Setting::CACHE_KEY);

            $message = $imported . ' settings imported successfully.';
            if (!empty($errors)) {
                $message .= ' Errors: ' . implode(' ', $errors);
                $type = 'warning';
            } else {
                $type = 'success';
            }

            return redirect()->route('settings.index')
                ->with($type, $message);

        } catch (\Exception $e) {
            return redirect()->route('settings.index')
                ->with('error', 'Failed to import settings: ' . $e->getMessage());
        }
    }
}
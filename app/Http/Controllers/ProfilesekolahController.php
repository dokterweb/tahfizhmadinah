<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profilesekolah;
use Illuminate\Support\Facades\Storage;

class ProfilesekolahController extends Controller
{
    public function edit()
    {
        $profile = Profilesekolah::first(); // single record
        return view('dashboard.profileedit', compact('profile'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_sekolah'  => 'nullable|string|max:255',
            'alamat'        => 'nullable|string',
            'phone'         => 'nullable|string|max:50',
            'logo'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $profile = Profilesekolah::first();
        if (!$profile) {
            $profile = new Profilesekolah();
        }

        // handle logo upload
        if ($request->hasFile('logo')) {
            // delete old logo
            if ($profile->logo_path && Storage::disk('public')->exists($profile->logo_path)) {
                Storage::disk('public')->delete($profile->logo_path);
            }

            $path = $request->file('logo')->store('school', 'public'); // storage/app/public/school/...
            $profile->logo_path = $path;
        }

        $profile->fill($request->only(['nama_sekolah','alamat','phone']));
        $profile->save();

        return redirect()->route('settings.school.edit')->with('success', 'Profile sekolah disimpan.');
    }
}

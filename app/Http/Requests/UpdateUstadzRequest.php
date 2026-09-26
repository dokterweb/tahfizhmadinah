<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUstadzRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $ustadz = $this->route('ustadz'); // Ambil ustadz dari route parameter

        return [
            'name'          => ['required', 'string', 'max:255'],
            'avatar'        => ['nullable','image','mimes:png,jpg,jpeg'],
            'email'         => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users')->ignore($ustadz->user->id), // Abaikan email siswa yang sedang diupdate
            ],
            'password'      => ['nullable', 'string', 'min:6'],
            'kelamin'       => ['required', 'string', 'in:laki-laki,perempuan'], 
            'tempat_lahir'  => ['required', 'string', 'max:255'],
            'tgl_lahir'     => ['required','date'],
            'no_hp'         => ['required','string','max:100'],
            'sub_kelas_ids'   => ['required', 'array', 'min:1'],
            'sub_kelas_ids.*' => ['integer', 'exists:sub_kelas,id'],
        ];
    }
}

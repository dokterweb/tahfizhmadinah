<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreSiswaRequest extends FormRequest
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
        return [
            'name'          => ['required', 'string', 'max:255'],
            'avatar'        => ['nullable','image','mimes:png,jpg,jpeg'],
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password'      => ['required', 'string', 'min:6'],
            'sub_kelas_id'  => ['required','integer','exists:sub_kelas,id'],
            'ustadz_id'     => ['required','integer','exists:ustadzs,id'],
            'kelamin'       => ['required', 'string', 'in:laki-laki,perempuan'], 
            'tempat_lahir'  => ['required', 'string', 'max:255'],
            'tgl_lahir'     => ['required','date'],
            'alamat'        => ['required','string','max:65535'],
            'no_hp'         => ['required','string','max:100'],
        ];
    }
}

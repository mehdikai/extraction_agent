<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExtractDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file'      => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'doc_type'  => 'sometimes|string|in:carte_grise,carte_verte,permis_conduire,constat_amiable',
        ];
    }
}
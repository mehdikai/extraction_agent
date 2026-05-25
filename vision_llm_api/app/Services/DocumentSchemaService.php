<?php
namespace App\Services;

class DocumentSchemaService
{
    public function getPrompt(string $docType): string
    {
        return match($docType) {
            'carte_grise'      => $this->carteGrisePrompt(),
            'carte_verte'      => $this->carteVertePrompt(),
            'permis_conduire'  => $this->permisPrompt(),
            'constat_amiable'  => $this->constatPrompt(),
            default            => throw new \InvalidArgumentException("Unknown doc type: $docType"),
        };
    }

    private function carteGrisePrompt(): string
    {
        return <<<PROMPT
You are an expert at extracting data from French vehicle registration certificates (Carte Grise / Certificat d'immatriculation).

Extract ALL fields from this file and return ONLY a valid JSON object with no markdown, no explanation.

Rules:
- If a field is not visible or blank, return null for that field
- Dates must be in DD/MM/YYYY format
- The registration number (champ A) format is typically AB-123-CD or 12-AB-123 for older ones

JSON schema to return:
{
  "document_type": "carte_grise",
  "status": "success",
  "data": {
    "numero_immatriculation": null,
    "date_premiere_immatriculation": null,
    "proprietaire_nom": null,
    "proprietaire_prenom": null,
    "proprietaire_adresse": null,
    "marque": null,
    "modele": null,
    "numero_chassis_vin": null
  }
}
PROMPT;
    }

    private function carteVertePrompt(): string
    {
        return <<<PROMPT
You are an expert at extracting data from French insurance certificates (Carte Verte / Attestation d'assurance).

Extract ALL fields and return ONLY a valid JSON object with no markdown, no explanation.

Rules:
- If a field is not visible or blank, return null
- Dates in DD/MM/YYYY format
- The contract number may contain spaces and letters

JSON schema to return:
{
  "document_type": "carte_verte",
  "status": "success",
  "data": {
    "assurance_compagnie": null,
    "numero_contrat_police": null,
    "valable_du": null,
    "valable_au": null,
    "numero_immatriculation": null,
    "marque_vehicule": null,
    "assure_nom_complet": null,
    "assure_adresse": null
  }
}
PROMPT;
    }

    private function permisPrompt(): string
    {
        return <<<PROMPT
You are an expert at extracting data from French driving licenses (Permis de conduire), both old and new format.

Extract ALL fields and return ONLY a valid JSON object with no markdown, no explanation.

Rules:
- If a field is blank, return null
- Dates in DD/MM/YYYY format
- "categories_obtenues" must be an array of strings (e.g. ["B", "A1"]) — only include categories that are explicitly visible and validated on the document

JSON schema to return:
{
  "document_type": "permis_conduire",
  "status": "success",
  "data": {
    "nom": null,
    "prenom": null,
    "date_naissance": null,
    "lieu_naissance": null,
    "date_delivrance": null,
    "date_expiration": null,
    "numero_permis": null,
    "categories_obtenues": []
  }
}
PROMPT;
    }

    private function constatPrompt(): string
    {
        return <<<PROMPT
You are an expert at extracting data from French accident reports (Constat Amiable d'Accident).

This document has a specific layout:
- LEFT column (blue header) = Vehicle A information
- RIGHT column (yellow header) = Vehicle B information  
- CENTER section = numbered checkboxes for circumstances (cases à cocher)
- TOP section = general accident information

Pay extreme attention to which column each piece of information belongs to. Do NOT mix Vehicle A and Vehicle B data.

For "circonstances_cochees": return an array of integers corresponding to the numbered boxes that are checked/ticked (e.g. [8] means box number 8 is checked). If none are checked, return [].

Extract ALL fields and return ONLY a valid JSON object with no markdown, no explanation.

Rules:
- Handwriting must be read carefully — take your time
- If a field is blank or illegible, return null
- Dates in DD/MM/YYYY format, time in HH:MM format
- Boolean fields: true if the box is checked, false if not

JSON schema to return:
{
  "document_type": "constat_amiable",
  "status": "success",
  "data": {
    "informations_generales": {
      "date_accident": null,
      "heure_accident": null,
      "lieu_pays": null,
      "lieu_exact": null,
      "blesses": false,
      "degats_materiels_autres": false,
      "temoins": null
    },
    "vehicule_a": {
      "assure_nom": null,
      "assure_prenom": null,
      "assure_telephone": null,
      "vehicule_marque_type": null,
      "vehicule_immatriculation": null,
      "assurance_nom": null,
      "assurance_numero_contrat": null,
      "conducteur_nom": null,
      "conducteur_prenom": null,
      "conducteur_numero_permis": null,
      "degats_apparents": null,
      "observations": null,
      "circonstances_cochees": []
    },
    "vehicule_b": {
      "assure_nom": null,
      "assure_prenom": null,
      "assure_telephone": null,
      "vehicule_marque_type": null,
      "vehicule_immatriculation": null,
      "assurance_nom": null,
      "assurance_numero_contrat": null,
      "conducteur_nom": null,
      "conducteur_prenom": null,
      "conducteur_numero_permis": null,
      "degats_apparents": null,
      "observations": null,
      "circonstances_cochees": []
    }
  }
}
PROMPT;
    }
}
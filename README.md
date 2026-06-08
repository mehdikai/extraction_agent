
```
# Extraction Agent 📄

A powerful document extraction system designed to intelligently extract structured data from French legal and administrative documents using Vision LLM APIs.

## 🎯 Overview

Extraction Agent leverages cutting-edge AI technologies to automatically extract and structure information from:

- **Carte Grise** (Certificate of Registration) - Vehicle registration documents
- **Carte Verte** (Green Card) - Vehicle insurance attestations
- **Permis de Conduire** (Driving License) - Driver's licenses (new and old formats)
- **Constat Amiable d'Accident** (Accident Report) - Insurance accident reports

The system implements multiple extraction approaches to optimize for accuracy, speed, and cost-effectiveness.

## 🚀 Key Features

- **Vision LLM Integration**: Direct visual document processing using state-of-the-art multimodal AI models
- **Structured Output**: Extracts data into predefined, validated JSON schemas
- **French Document Specialization**: Optimized prompts and validation for French legal documents
- **Laravel Backend**: RESTful API built with Laravel for easy integration
- **Comprehensive Test Suite**: Includes test documents for all supported document types

## 📐 Architecture


Sends images directly to multimodal vision models:

```
Image/PDF → Vision LLM API → Structured JSON
```

**Advantages:**
- Single architecture (PHP only)
- Better handwriting recognition
- Respects document layout and columns

**Trade-offs:**
- Slightly higher API costs
- Faster processing (fewer hops)


## 📋 Supported Document Schemas

### 1. Carte Grise
```json
{
  "document_type": "carte_grise",
  "data": {
    "numero_immatriculation": "AB-123-CD",
    "date_premiere_immatriculation": "05/01/1998",
    "proprietaire_nom": "DUPONT",
    "proprietaire_prenom": "YVES",
    "proprietaire_adresse": "27 RUE DES ROITELETS, 59169 FERIN",
    "marque": "RENAULT",
    "modele": "CLIO",
    "numero_chassis_vin": "VF1BS400523456789"
  }
}
```

### 2. Carte Verte
```json
{
  "document_type": "carte_verte",
  "data": {
    "assurance_compagnie": "MAIF",
    "numero_contrat_police": "F 567 32",
    "valable_du": "01/01/2018",
    "valable_au": "31/12/2019",
    "numero_immatriculation": "AA-123-AA",
    "marque_vehicule": "PEUGEOT",
    "assure_nom_complet": "NOM DE L'ASSURÉ",
    "assure_adresse": "75000 VILLE"
  }
}
```

### 3. Permis de Conduire
```json
{
  "document_type": "permis_conduire",
  "data": {
    "nom": "MARTIN",
    "prenom": "Paul",
    "date_naissance": "14/07/1981",
    "lieu_naissance": "Utopia city",
    "date_delivrance": "01/01/2013",
    "date_expiration": "31/12/2028",
    "numero_permis": "13AA00002",
    "categories_obtenues": ["AM", "A", "A1", "B1", "B"]
  }
}
```

### 4. Constat Amiable d'Accident
```json
{
  "document_type": "constat_amiable",
  "data": {
    "informations_generales": {
      "date_accident": "03/10/2024",
      "heure_accident": "12:14",
      "lieu_pays": "France",
      "lieu_exact": "Rue de la Libération, 42000 Saint Etienne",
      "blesses": false,
      "degats_materiels_autres": false
    },
    "vehicule_a": { /* vehicle details */ },
    "vehicule_b": { /* vehicle details */ }
  }
}
```

## 🛠️ Tech Stack

- **Backend**: Laravel 11
- **Language**: PHP 8.3+
- **API Integration**:
  - NVIDIA Vision API
  - Claude Vision (Anthropic)
  - GPT-4o (OpenAI)
- **Optional OCR**: Docling (Python)
- **Testing**: PHPUnit, Pest
- **Build Tools**: Vite

## 📦 Installation

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 18+ (for Vite)
- API keys for vision models (NVIDIA, Anthropic, or OpenAI)

### Setup

1. **Clone and install dependencies**
```bash
cd vision_llm_api
composer install
npm install
```

2. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Set API credentials in `.env`**
```env
NVIDIA_API_KEY=your_nvidia_api_key
ANTHROPIC_API_KEY=your_claude_api_key
OPENAI_API_KEY=your_openai_api_key
```

4. **Run migrations**
```bash
php artisan migrate
```

5. **Start the development server**
```bash
php artisan serve
# In another terminal:
npm run dev
```

## 🔌 API Endpoints

### Document Extraction

**POST** `/api/extract`

Extract data from a document using the default vision approach:

```bash
curl -X POST http://localhost:8000/api/extract \
  -F "document=@/path/to/carte_grise.jpg" \
  -F "document_type=carte_grise"
```

**Response:**
```json
{
  "status": "success",
  "document_type": "carte_grise",
  "processing_time_ms": 2340,
  "confidence": 0.95,
  "data": {
    "numero_immatriculation": "AB-123-CD",
    "date_premiere_immatriculation": "05/01/1998",
    ...
  }
}
```

## 📊 Performance Comparison

| Criteria | Approach A (Docling → Text LLM) | Approach B (Vision LLM) | Hybrid |
|----------|--------------------------------|----------------------|--------|
| **Accuracy (Handwriting)** | Medium | High | Very High |
| **Accuracy (Layout)** | Medium | High | Very High |
| **Latency** | High (5-10s) | Low (2-4s) | Medium (3-5s) |
| **Cost** | Low | Medium | Medium |
| **Architecture Complexity** | High | Low | Medium |
| **Infrastructure** | PHP + Python | PHP only | PHP only |

## 🧪 Testing

### Run Tests
```bash
./vendor/bin/phpunit
# or
npm run test:unit
```

### Test Documents

Sample documents are provided in `/test_docs/`:

```
test_docs/
├── carte_grise/         # Car registration samples
├── carte_verte/         # Insurance attestation samples
├── permis/              # Driving license samples
└── sinistre/            # Accident report samples
    └── 2page/           # Multi-page accident reports
```

### Manual Testing

Use the `/api/test` endpoint to test extraction with sample documents:

```bash
curl http://localhost:8000/api/test/carte_grise
```

## 🔒 Security

- Input validation on all file uploads
- File type verification (MIME type checking)
- Base64 encoding for API transmission
- Environment-based configuration
- Secure credential storage via `.env`

## 📂 Project Structure

```
extraction_agent/
├── vision_llm_api/              # Main Laravel application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── ExtractionController.php
│   │   │   └── Requests/
│   │   │       └── ExtractDocumentRequest.php
│   │   └── Services/
│   │       ├── DocumentSchemaService.php
│   │       └── VisionExtractionService.php
│   ├── config/
│   ├── routes/
│   │   └── api.php
│   └── tests/
├── test_docs/                   # Test documents
├── docs/                        # Documentation
├── notes.txt                    # Development notes
└── schema.txt                   # JSON schema definitions
```

## 🚧 Development Roadmap

- [ ] Phase 1: Vision LLM direct extraction (Approach B)
- [ ] Phase 2: Docling OCR integration (Approach A)
- [ ] Phase 3: Hybrid approach implementation
- [ ] Phase 4: Performance evaluation and optimization
- [ ] Multi-page document support
- [ ] Batch processing API
- [ ] Dashboard for extraction monitoring
- [ ] WebSocket real-time extraction feedback

## 🤝 Contributing

1. Create a feature branch (`git checkout -b feature/amazing-feature`)
2. Commit changes (`git commit -m 'Add amazing feature'`)
3. Push to branch (`git push origin feature/amazing-feature`)
4. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📚 Documentation

- [API Documentation](./vision_llm_api/README.md)
- [Development Notes](./notes.txt)
- [Schema Definitions](./schema.txt)

## 🆘 Troubleshooting

### Issue: API returns "Invalid API Key"
- Verify your API credentials in `.env`
- Check token expiration in your API provider account
- Ensure the API endpoint URL is correct

### Issue: Poor extraction accuracy
- Try switching between Approach A and B for comparison
- Ensure document images are clear and well-lit
- Check document is one of the supported types
- Review extraction prompts in `DocumentSchemaService.php`

### Issue: Handwriting not extracted
- Vision LLM Approach B is recommended for handwritten content
- Ensure document image quality is high
- Check model selected supports detailed handwriting recognition

## 📞 Support

For issues, feature requests, or questions:
- Open an issue on GitHub
- Check existing documentation in `/docs`
- Review development notes in `notes.txt`

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com)
- Vision APIs: NVIDIA NIM, OpenAI, Anthropic Claude
- OCR: Docling
- Inspired by document extraction best practices in fintech

---

**Version:** 1.0.0  
**Last Updated:** June 2026  
**Status:** Active Development
```
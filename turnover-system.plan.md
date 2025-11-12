# Exit Interview Form and PDF Generation for Turnovers

## Overview

Add an Employee Exit Interview form that appears after `interview_notes` and `interviewed_by` are filled in the turnover create/edit flow. The form will be bilingual (English/Arabic), collect answers to 23 questions (6 rating questions 1-5, 13 rating questions 7-19, 4 open text questions 20-23), store answers as JSON, and automatically generate a PDF matching the provided template.

## Implementation Steps

### 1. Database Schema

- **File**: Create new migration `add_interview_answers_to_turnovers_table.php`
- Add `interview_answers` JSON column to `turnovers` table to store all interview responses
- Update `Turnover` model to include `interview_answers` in `$fillable` and cast it as `array`

### 2. Form Request Validation

- **File**: Create `app/Http/Requests/StoreInterviewAnswersRequest.php`
- Validate all 23 questions:
- Questions 1-6, 7-19: rating (1-5, required)
- Questions 20-23: text fields (nullable, max length)
- Employee name, date, signature (required for PDF)

### 3. Controller Methods

- **File**: `app/Http/Controllers/TurnoverController.php`
- Add `showInterviewForm(Turnover $turnover)` method to display the interview form
- Add `storeInterviewAnswers(StoreInterviewAnswersRequest $request, Turnover $turnover)` method to:
- Validate and store answers as JSON in `interview_answers`
- Generate PDF using dompdf
- Save PDF to `storage/app/uploads/turnover-reports/`
- Update `turnover_pdf_path` with the saved path
- Redirect with success message

### 4. Interview Form View

- **File**: `resources/views/turnovers/interview.blade.php`
- Create bilingual form matching the template structure:
- **General Information Section**: Pre-fill from turnover data (name, position, department, dates, etc.)
- **Interview Information Section**: Display disclaimer text (bilingual)
- **Evaluation Table**: Questions 1-6 with rating scale (1-5) in table format
- **Rating Questions**: Questions 7-19 with rating scale (1-5)
- **Open Text Questions**: Questions 20-23 with textarea fields
- **Employee Signature Section**: Name, date, signature fields
- Use Bootstrap styling to match existing design
- Include Arabic text support (RTL where needed)

### 5. PDF Template View

- **File**: `resources/views/turnovers/pdf/interview.blade.php`
- Create PDF template matching the three provided images:
- **Page 1**: General Information section, Interview Information section, Evaluation Table (Questions 1-6)
- **Page 2**: Rating Questions (7-19) in table format
- **Page 3**: Open Text Questions (20-23), Employee Signature section
- Use inline CSS for PDF styling (dompdf compatible)
- Bilingual layout (English left, Arabic right)
- Green header bars matching template
- Rating checkboxes/circles for 1-5 scale

### 6. PDF Generation Service

- **File**: `app/Services/TurnoverPdfService.php` (optional helper class)
- Method to generate PDF using `barryvdh/laravel-dompdf`
- Handle file storage in `storage/app/uploads/turnover-reports/`
- Return file path for database storage

### 7. Integration with Create/Edit Flow

- **Files**: 
- `resources/views/turnovers/create.blade.php`
- `resources/views/turnovers/edit.blade.php`
- Add conditional display: After `interview_notes` and `interviewed_by` are filled, show a button/link to "Complete Exit Interview"
- Link to interview form route: `route('turnovers.interview', $turnover)`
- Show indicator if interview is already completed

### 8. Routes

- **File**: `routes/web.php`
- Add route: `GET /turnovers/{turnover}/interview` → `TurnoverController@showInterviewForm`
- Add route: `POST /turnovers/{turnover}/interview` → `TurnoverController@storeInterviewAnswers`

### 9. Localization

- **Files**: 
- `resources/lang/fr/messages.php`
- `resources/lang/en/messages.php`
- Add translations for:
- Interview form labels
- All 23 questions (English and Arabic text)
- Form buttons and messages
- PDF generation messages

### 10. Index/Edit Views Updates

- **File**: `resources/views/turnovers/index.blade.php`
- Add column or indicator showing if interview is completed
- Add download link for PDF if `turnover_pdf_path` exists
- **File**: `resources/views/turnovers/edit.blade.php`
- Show interview status and link to complete/view interview

## Technical Details

- Use `barryvdh/laravel-dompdf` (already installed) for PDF generation
- Store PDFs in `storage/app/uploads/turnover-reports/` directory
- JSON structure for `interview_answers`: `{q1: 4, q2: 5, ..., q20: "text answer", ...}`
- Ensure Arabic text renders correctly in PDF (use UTF-8 compatible fonts)
- Handle file permissions for uploads directory
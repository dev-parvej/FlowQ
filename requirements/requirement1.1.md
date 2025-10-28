# Requirement 1.1: Question Required Option with Skip Functionality

## Overview
Add a "required" option to questions that allows users to mark questions as optional. When a question is not required, display a skip button in the survey UI that allows participants to bypass the question and proceed to the next one.

## Database Changes

### Questions Table Schema Modification
Modify existing schema to include in `flowq_questions` table:
- `is_required` (BOOLEAN, DEFAULT TRUE)
- `skip_next_question_id` (INT, NULLABLE) - Question ID to navigate to when skipped

### Schema Updates (No Migration Required)
- Update table creation schema directly in plugin installation
- Plugin is in development phase, no existing production data to migrate

## User Interface Changes

### Admin Interface (Question Builder)
- Add checkbox input: "This question is required"
- Default state: checked (required = true)
- Position: Below question description field
- Label: "This question is required"
- When unchecked, show additional field for "Skip destination"

### Skip Destination Field
- Dropdown select: "Next question when skipped"
- Options: List of available questions in survey + "End survey"
- Only visible when "This question is required" is unchecked
- Default: Next question in sequence

### Frontend Survey Interface
- For non-required questions, display "Skip" button alongside answer options
- Skip button styling: Secondary button style, positioned after answer options
- Skip button text: "Skip this question"

## Functionality Requirements

### Skip Logic
1. When skip button is clicked:
   - Record skip action in session/response data
   - Navigate to designated "next question" or end survey
   - Do not validate answer selection for skipped questions

### Data Storage
- Track skipped questions in `flowq_responses` table
- Store skip status: `is_skipped` boolean field
- Maintain navigation flow integrity

### Validation Updates
- Required questions: Must have answer before proceeding (existing behavior)
- Non-required questions: Can proceed with or without answer
- Skip questions: Bypass validation entirely

## Technical Implementation

### Backend Changes
1. **Question Manager Class** (`class-question-manager.php`)
   - Update question creation/update methods to handle `is_required` field
   - Add `skip_next_question_id` field for skip destination

2. **Database Schema**
   - Modify existing table creation script to include new columns
   - Update question model structure

3. **Frontend Handler**
   - Add skip button rendering logic
   - Implement skip action AJAX handler
   - Update navigation flow to handle skip destinations

### Frontend Changes
1. **Question Display**
   - Conditionally render skip button for non-required questions
   - Update form validation to skip required validation for skipped questions

2. **Navigation Logic**
   - Handle skip navigation separately from answer-based navigation
   - Ensure skip destination takes precedence over answer-based next question

## User Experience

### Admin Flow
1. Create/edit question
2. Uncheck "This question is required"
3. Select destination question for skip action
4. Save question

### Participant Flow
1. View question (required or optional)
2. For optional questions: Choose to answer or skip
3. If skip: Navigate to designated next question
4. If answer: Follow normal answer-based navigation

## Acceptance Criteria

### Database
- [ ] `is_required` column added to questions table schema (default TRUE)
- [ ] `skip_next_question_id` column added to questions table schema
- [ ] Table creation script updated with new columns
- [ ] `is_skipped` field added to responses table schema

### Admin Interface
- [ ] Required checkbox displayed in question builder
- [ ] Skip destination dropdown shown when question is not required
- [ ] Proper form validation and saving of new fields

### Frontend Interface
- [ ] Skip button displayed only for non-required questions
- [ ] Skip button properly styled and positioned
- [ ] Skip action navigates to correct destination

### Functionality
- [ ] Required questions enforce answer validation
- [ ] Non-required questions allow proceeding with/without answers
- [ ] Skip action bypasses validation and navigates correctly
- [ ] Skip status recorded in database
- [ ] Existing survey flow remains unaffected for required questions

## Dependencies
- Existing question management system
- Current survey navigation flow
- Database migration system
- AJAX handling infrastructure

## Development Notes
- Plugin is in active development phase
- Schema modifications will be applied directly to table creation scripts
- No backward compatibility concerns as no production data exists
- New installations will include updated schema automatically
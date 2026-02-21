# Requirements Document

## Introduction

The canoe-app-funds system is a fund management API back-end service that tracks investment funds, their managers, and the companies they invest in. The system provides CRUD operations, duplicate detection through event-driven architecture, and a user interface for data interaction.

## Glossary

- **Fund**: An investment vehicle with a name, start year, manager, and optional aliases
- **Fund_Manager**: An investment management company that manages zero or more Funds
- **Company**: An investment recipient that can receive investments from multiple Funds
- **Alias**: An alternative name for a Fund, unique to that Fund
- **Duplicate_Warning**: An event indicating potential duplicate Fund entries
- **API**: The back-end service providing RESTful endpoints
- **UI**: The Vue.js front-end interface for user interaction
- **Soft_Delete**: Marking a record as deleted without physically removing it from the database

## Requirements

### Requirement 1: Fund Data Model

**User Story:** As a developer, I want a well-defined data model for Funds, so that I can store and retrieve fund information consistently.

#### Acceptance Criteria

1. THE API SHALL store Fund records with name, start year, and Fund_Manager reference
2. THE API SHALL associate each Fund with exactly one Fund_Manager
3. THE API SHALL associate each Fund with zero or more Aliases
4. THE API SHALL ensure each Alias belongs to exactly one Fund
5. THE API SHALL ensure Alias names do not match any Fund name or other Alias name across the system
6. THE API SHALL associate each Fund with zero or more Companies

### Requirement 2: Fund Manager Data Model

**User Story:** As a developer, I want a data model for Fund Managers, so that I can track which companies manage which funds.

#### Acceptance Criteria

1. THE API SHALL store Fund_Manager records with a name
2. THE API SHALL allow a Fund_Manager to be associated with zero or more Funds

### Requirement 3: Company Data Model

**User Story:** As a developer, I want a data model for Companies, so that I can track investment recipients.

#### Acceptance Criteria

1. THE API SHALL store Company records with a name
2. THE API SHALL allow a Company to be associated with zero or more Funds

### Requirement 4: Create Fund

**User Story:** As a user, I want to create a new Fund with its details, so that I can track new investment vehicles.

#### Acceptance Criteria

1. WHEN a valid Fund creation request is received, THE API SHALL create a Fund record with name, start year, and Fund_Manager
2. WHEN a Fund creation request includes Aliases, THE API SHALL create the associated Alias records
3. WHEN a Fund creation request includes Companies, THE API SHALL create the associations between the Fund and Companies
4. IF a Fund creation request has invalid data, THEN THE API SHALL return a descriptive error message
5. WHEN a Fund is created, THE API SHALL trigger duplicate detection processing

### Requirement 5: List Funds

**User Story:** As a user, I want to list Funds with optional filters, so that I can find specific funds quickly.

#### Acceptance Criteria

1. WHEN a list request is received, THE API SHALL return all non-deleted Funds
2. WHERE a name filter is provided, THE API SHALL return only Funds matching the name
3. WHERE a Fund_Manager filter is provided, THE API SHALL return only Funds managed by that Fund_Manager
4. WHERE a year filter is provided, THE API SHALL return only Funds with that start year
5. WHERE a Company filter is provided, THE API SHALL return only Funds associated with that Company
6. THE API SHALL exclude soft-deleted Funds from list results

### Requirement 6: Update Fund

**User Story:** As a user, I want to update a Fund and its related attributes, so that I can keep fund information current.

#### Acceptance Criteria

1. WHEN a valid Fund update request is received, THE API SHALL update the Fund record
2. WHEN an update includes Alias changes, THE API SHALL add, update, or remove Aliases accordingly
3. WHEN an update includes Company association changes, THE API SHALL add or remove Company associations accordingly
4. IF a Fund update request references a non-existent Fund, THEN THE API SHALL return an error message
5. WHEN a Fund is updated, THE API SHALL trigger duplicate detection processing

### Requirement 7: Soft Delete Fund

**User Story:** As a user, I want to soft delete a Fund, so that I can remove it from active use without losing historical data.

#### Acceptance Criteria

1. WHEN a Fund delete request is received, THE API SHALL mark the Fund as deleted
2. WHEN a Fund is soft-deleted, THE API SHALL preserve all associated Aliases and Company associations
3. THE API SHALL exclude soft-deleted Funds from list and filter operations

### Requirement 8: Soft Delete Fund Manager

**User Story:** As a user, I want to soft delete a Fund Manager, so that I can handle companies that are no longer active.

#### Acceptance Criteria

1. WHEN a Fund_Manager delete request is received, THE API SHALL mark the Fund_Manager as deleted
2. WHEN a Fund_Manager is soft-deleted, THE API SHALL preserve associations with existing Funds
3. IF a Fund_Manager has associated Funds, THEN THE API SHALL allow the deletion and maintain referential integrity
4. THE API SHALL exclude soft-deleted Fund_Managers from list operations

### Requirement 9: Soft Delete Company

**User Story:** As a user, I want to soft delete a Company, so that I can handle investment recipients that are no longer relevant.

#### Acceptance Criteria

1. WHEN a Company delete request is received, THE API SHALL mark the Company as deleted
2. WHEN a Company is soft-deleted, THE API SHALL preserve associations with existing Funds
3. THE API SHALL exclude soft-deleted Companies from list operations

### Requirement 10: Duplicate Detection Event Emission

**User Story:** As a system administrator, I want the system to detect potential duplicate Funds, so that I can maintain data quality.

#### Acceptance Criteria

1. WHEN a Fund is created or updated, THE API SHALL check for name matches against existing Funds with the same Fund_Manager
2. WHEN checking for duplicates, THE API SHALL perform case-insensitive comparison of Fund names and Aliases
3. IF a Fund name matches an existing Fund name or Alias for the same Fund_Manager, THEN THE API SHALL emit a duplicate_fund_warning event to Redis
4. IF a Fund Alias matches an existing Fund name or Alias for the same Fund_Manager, THEN THE API SHALL emit a duplicate_fund_warning event to Redis
5. THE API SHALL include both Fund identifiers in the duplicate_fund_warning event payload

### Requirement 11: Duplicate Warning Consumer

**User Story:** As a system administrator, I want duplicate warnings to be persisted, so that I can review and resolve them.

#### Acceptance Criteria

1. WHEN a duplicate_fund_warning event is received, THE Duplicate_Warning_Consumer SHALL persist the warning to the database
2. THE Duplicate_Warning_Consumer SHALL store both Fund identifiers and the timestamp
3. THE Duplicate_Warning_Consumer SHALL mark warnings as unresolved by default

### Requirement 12: List Duplicate Warnings

**User Story:** As a system administrator, I want to view unresolved duplicate warnings, so that I can review and address potential data quality issues.

#### Acceptance Criteria

1. WHEN a duplicate warnings list request is received, THE API SHALL return all unresolved Duplicate_Warnings
2. THE API SHALL include Fund details for both Funds in each warning
3. THE API SHALL exclude resolved warnings from the list

### Requirement 13: User Interface

**User Story:** As a user, I want a web interface to interact with the fund data, so that I can manage funds without using API calls directly.

#### Acceptance Criteria

1. THE UI SHALL display a list of Funds with their details
2. THE UI SHALL provide forms to create new Funds with Aliases and Company associations
3. THE UI SHALL provide forms to update existing Funds
4. THE UI SHALL provide controls to delete Funds, Fund_Managers, and Companies
5. THE UI SHALL display filters for Fund listing by name, Fund_Manager, year, and Company
6. THE UI SHALL display unresolved duplicate warnings

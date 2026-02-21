# Implementation Plan: canoe-app-funds

## Overview

This implementation plan covers the development of a Laravel-based fund management API with event-driven duplicate detection, SQLite database, Redis event bus, and Vue.js frontend. The system manages investment funds, fund managers, and companies with comprehensive CRUD operations, soft delete support, and asynchronous duplicate warning generation.

## Tasks

- [x] 1. Set up Laravel project and core infrastructure
  - [x] 1.1 Initialize Laravel project with required dependencies
    - Create new Laravel project
    - Install Redis package (predis/predis)
    - Configure SQLite database connection in .env
    - Configure Redis connection for events
    - Set up queue configuration for Redis driver
    - _Requirements: All (foundation for entire system)_
  
  - [x] 1.2 Create database migrations for all tables
    - Create migration for fund_managers table with soft deletes
    - Create migration for companies table with soft deletes
    - Create migration for funds table with soft deletes and foreign keys
    - Create migration for aliases table with unique constraint
    - Create migration for company_fund pivot table
    - Create migration for duplicate_warnings table
    - Add all specified indexes from design
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 2.1, 2.2, 3.1, 3.2, 11.2_
  
  - [ ]* 1.3 Write property test for database schema
    - **Property 1: Entity Persistence Round-Trip**
    - **Validates: Requirements 1.1, 2.1, 3.1**
    - Test Fund, FundManager, and Company creation and retrieval
    - Run 100 iterations with randomized data using Faker

- [x] 2. Implement Eloquent models and relationships
  - [x] 2.1 Create FundManager model with relationships
    - Implement SoftDeletes trait
    - Define fillable fields
    - Define hasMany relationship to Fund
    - _Requirements: 2.1, 2.2_
  
  - [x] 2.2 Create Company model with relationships
    - Implement SoftDeletes trait
    - Define fillable fields
    - Define belongsToMany relationship to Fund
    - _Requirements: 3.1, 3.2_
  
  - [x] 2.3 Create Fund model with relationships
    - Implement SoftDeletes trait
    - Define fillable fields and casts
    - Define belongsTo relationship to FundManager
    - Define hasMany relationship to Alias
    - Define belongsToMany relationship to Company
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.6_
  
  - [x] 2.4 Create Alias model with relationships
    - Define fillable fields
    - Define belongsTo relationship to Fund
    - Add unique validation rule for name
    - _Requirements: 1.3, 1.4, 1.5_
  
  - [x] 2.5 Create DuplicateWarning model with relationships
    - Define fillable fields and casts
    - Define belongsTo relationships for fund1 and fund2
    - Implement unresolved() query scope
    - _Requirements: 11.1, 11.2, 11.3, 12.3_
  
  - [ ]* 2.6 Write property test for referential integrity
    - **Property 2: Fund-Manager Referential Integrity**
    - **Validates: Requirements 1.2**
    - Test that every Fund has exactly one valid FundManager
    - Run 100 iterations with randomized data
  
  - [ ]* 2.7 Write property test for relationship cardinality
    - **Property 4: Relationship Cardinality Preservation**
    - **Validates: Requirements 1.3, 1.4, 1.6, 2.2, 3.2**
    - Test that Fund retrieval returns correct number of aliases and companies
    - Run 100 iterations with varying relationship counts

- [x] 3. Create Laravel factories for test data generation
  - [x] 3.1 Create FundManager factory
    - Generate realistic fund manager names using Faker
    - Add deleted() state for soft-deleted records
    - _Requirements: 2.1_
  
  - [x] 3.2 Create Company factory
    - Generate realistic company names using Faker
    - Add deleted() state for soft-deleted records
    - _Requirements: 3.1_
  
  - [x] 3.3 Create Fund factory
    - Generate fund names, start years using Faker
    - Associate with FundManager factory
    - Add withAliases() method for creating funds with aliases
    - Add withCompanies() method for company associations
    - Add deleted() state for soft-deleted records
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.6_
  
  - [x] 3.4 Create Alias factory
    - Generate unique alias names using Faker
    - Associate with Fund factory
    - _Requirements: 1.3, 1.4, 1.5_
  
  - [x] 3.5 Create DuplicateWarning factory
    - Associate with two Fund instances
    - Set resolved to false by default
    - _Requirements: 11.1, 11.2, 11.3_

- [-] 4. Implement duplicate detection service
  - [x] 4.1 Create DuplicateDetectionService class
    - Implement checkForDuplicates() method
    - Query funds by same fund_manager_id excluding current fund
    - Perform case-insensitive comparison of names and aliases
    - Emit DuplicateFundWarning event when match found
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_
  
  - [x] 4.2 Write property test for duplicate detection
    - **Property 7: Duplicate Detection Event Emission**
    - **Validates: Requirements 4.5, 6.5, 10.1, 10.2, 10.3, 10.4, 10.5**
    - Test that matching names/aliases trigger events
    - Run 100 iterations with various duplicate scenarios
  
  - [ ]* 4.3 Write unit tests for duplicate detection edge cases
    - Test case-insensitive matching
    - Test alias-to-fund-name matching
    - Test fund-name-to-alias matching
    - Test no duplicate when different fund managers
    - Test no duplicate when no matches
    - _Requirements: 10.1, 10.2, 10.3, 10.4_

- [x] 5. Implement event system for duplicate warnings
  - [x] 5.1 Create DuplicateFundWarning event class
    - Define event payload with fund_id_1, fund_id_2, detected_at
    - Implement ShouldQueue interface for Redis queue
    - _Requirements: 10.5, 11.1_
  
  - [x] 5.2 Create DuplicateWarningListener
    - Subscribe to DuplicateFundWarning event
    - Persist warning to duplicate_warnings table
    - Set resolved to false by default
    - _Requirements: 11.1, 11.2, 11.3_
  
  - [x] 5.3 Register event and listener in EventServiceProvider
    - Map DuplicateFundWarning to DuplicateWarningListener
    - _Requirements: 11.1_
  
  - [ ]* 5.4 Write property test for warning persistence
    - **Property 15: Warning Persistence Completeness**
    - **Validates: Requirements 11.1, 11.2, 11.3**
    - Test that events result in database records
    - Run 100 iterations with randomized fund pairs
  
  - [ ]* 5.5 Write unit tests for event system
    - Test event emission from service
    - Test listener creates database record
    - Test event payload structure
    - _Requirements: 10.5, 11.1, 11.2, 11.3_

- [ ] 6. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 7. Implement Fund API endpoints
  - [x] 7.0 Create FundService class
    - Implement createFund() method with database transaction
    - Implement listFunds() method with filtering logic
    - Implement getFund() method with eager loading
    - Implement updateFund() method with database transaction
    - Implement deleteFund() method for soft delete
    - Trigger duplicate detection via DuplicateDetectionService
    - Handle all database operations and business rules
    - _Requirements: 4.1, 4.2, 4.3, 4.5, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 6.1, 6.2, 6.3, 6.5, 7.1, 7.2, 7.3_
  
  - [x] 7.1 Create FundController with store method (POST /api/funds)
    - Validate request data (name, start_year, fund_manager_id required)
    - Delegate to FundService->createFund()
    - Return 201 with created Fund including relationships
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_
  
  - [x] 7.2 Create FundController index method (GET /api/funds)
    - Validate query parameters
    - Delegate to FundService->listFunds() with filters
    - Return 200 with Fund array including relationships
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6_
  
  - [x] 7.3 Create FundController show method (GET /api/funds/{id})
    - Delegate to FundService->getFund()
    - Return 404 if not found or soft-deleted
    - Return 200 with Fund object
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.6_
  
  - [x] 7.4 Create FundController update method (PUT /api/funds/{id})
    - Validate request data
    - Delegate to FundService->updateFund()
    - Return 200 with updated Fund including relationships
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_
  
  - [x] 7.5 Create FundController destroy method (DELETE /api/funds/{id})
    - Delegate to FundService->deleteFund()
    - Return 204 No Content
    - _Requirements: 7.1, 7.2, 7.3_
  
  - [ ]* 7.6 Write property test for fund creation completeness
    - **Property 5: Fund Creation Completeness**
    - **Validates: Requirements 4.1, 4.2, 4.3**
    - Test that created funds have all specified data
    - Run 100 iterations with varying alias and company counts
  
  - [ ]* 7.7 Write property test for invalid data rejection
    - **Property 6: Invalid Data Rejection**
    - **Validates: Requirements 4.4**
    - Test that invalid requests return errors without creating records
    - Run 100 iterations with various invalid inputs
  
  - [ ]* 7.8 Write property test for soft delete exclusion
    - **Property 8: Soft Delete Exclusion**
    - **Validates: Requirements 5.1, 5.6, 7.3**
    - Test that soft-deleted funds don't appear in lists
    - Run 100 iterations with mixed deleted/active funds
  
  - [ ]* 7.9 Write property tests for filter correctness
    - **Property 9: Name Filter Correctness** (Requirements 5.2)
    - **Property 10: Fund Manager Filter Correctness** (Requirements 5.3)
    - **Property 11: Year Filter Correctness** (Requirements 5.4)
    - **Property 12: Company Filter Correctness** (Requirements 5.5)
    - Run 100 iterations per filter type with randomized data
  
  - [ ]* 7.10 Write property test for fund update completeness
    - **Property 13: Fund Update Completeness**
    - **Validates: Requirements 6.1, 6.2, 6.3**
    - Test that updates modify all specified fields and relationships
    - Run 100 iterations with various update scenarios
  
  - [ ]* 7.11 Write unit tests for Fund API endpoints
    - Test successful fund creation with aliases and companies
    - Test fund creation validation errors
    - Test fund listing without filters
    - Test fund listing with each filter type
    - Test fund retrieval by ID
    - Test fund update with relationship changes
    - Test fund soft delete
    - Test 404 responses for non-existent funds
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 6.1, 6.2, 6.3, 6.4, 7.1, 7.2, 7.3_

- [x] 8. Implement FundManager API endpoints
  - [x] 8.0 Create FundManagerService class
    - Implement createFundManager() method
    - Implement listFundManagers() method
    - Implement deleteFundManager() method for soft delete
    - Handle all database operations and business rules
    - _Requirements: 2.1, 8.1, 8.2, 8.3, 8.4_
  
  - [x] 8.1 Create FundManagerController with store method (POST /api/fund-managers)
    - Validate request data (name required, unique)
    - Delegate to FundManagerService->createFundManager()
    - Return 201 with created FundManager
    - _Requirements: 2.1_
  
  - [x] 8.2 Create FundManagerController index method (GET /api/fund-managers)
    - Delegate to FundManagerService->listFundManagers()
    - Return 200 with FundManager array
    - _Requirements: 2.1, 8.4_
  
  - [x] 8.3 Create FundManagerController destroy method (DELETE /api/fund-managers/{id})
    - Delegate to FundManagerService->deleteFundManager()
    - Return 204 No Content
    - _Requirements: 8.1, 8.2, 8.3, 8.4_
  
  - [ ]* 8.4 Write property test for soft delete preservation
    - **Property 14: Soft Delete Preservation** (partial)
    - **Validates: Requirements 7.1, 7.2, 8.1, 8.2**
    - Test that soft-deleted fund managers preserve relationships
    - Run 100 iterations with various relationship scenarios
  
  - [ ]* 8.5 Write unit tests for FundManager API endpoints
    - Test fund manager creation
    - Test fund manager listing excludes soft-deleted
    - Test fund manager soft delete with associated funds
    - Test unique name constraint
    - _Requirements: 2.1, 8.1, 8.2, 8.3, 8.4_

- [x] 9. Implement Company API endpoints
  - [x] 9.0 Create CompanyService class
    - Implement createCompany() method
    - Implement listCompanies() method
    - Implement deleteCompany() method for soft delete
    - Handle all database operations and business rules
    - _Requirements: 3.1, 9.1, 9.2, 9.3_
  
  - [x] 9.1 Create CompanyController with store method (POST /api/companies)
    - Validate request data (name required, unique)
    - Delegate to CompanyService->createCompany()
    - Return 201 with created Company
    - _Requirements: 3.1_
  
  - [x] 9.2 Create CompanyController index method (GET /api/companies)
    - Delegate to CompanyService->listCompanies()
    - Return 200 with Company array
    - _Requirements: 3.1, 9.3_
  
  - [x] 9.3 Create CompanyController destroy method (DELETE /api/companies/{id})
    - Delegate to CompanyService->deleteCompany()
    - Return 204 No Content
    - _Requirements: 9.1, 9.2, 9.3_
  
  - [ ]* 9.4 Write property test for soft delete preservation
    - **Property 14: Soft Delete Preservation** (partial)
    - **Validates: Requirements 9.1, 9.2**
    - Test that soft-deleted companies preserve relationships
    - Run 100 iterations with various relationship scenarios
  
  - [ ]* 9.5 Write unit tests for Company API endpoints
    - Test company creation
    - Test company listing excludes soft-deleted
    - Test company soft delete with associated funds
    - Test unique name constraint
    - _Requirements: 3.1, 9.1, 9.2, 9.3_

- [x] 10. Implement DuplicateWarning API endpoint
  - [x] 10.0 Create DuplicateWarningService class
    - Implement listUnresolvedWarnings() method
    - Query unresolved warnings using unresolved() scope
    - Eager load fund1 and fund2 relationships with full details
    - Handle all database operations and business rules
    - _Requirements: 12.1, 12.2, 12.3_
  
  - [x] 10.1 Create DuplicateWarningController with index method (GET /api/duplicate-warnings)
    - Delegate to DuplicateWarningService->listUnresolvedWarnings()
    - Return 200 with warning array including Fund details
    - _Requirements: 12.1, 12.2, 12.3_
  
  - [ ]* 10.2 Write property test for unresolved warnings filter
    - **Property 16: Unresolved Warnings Filter**
    - **Validates: Requirements 12.1, 12.2, 12.3**
    - Test that only unresolved warnings with fund details are returned
    - Run 100 iterations with mixed resolved/unresolved warnings
  
  - [ ]* 10.3 Write unit tests for DuplicateWarning API endpoint
    - Test listing returns only unresolved warnings
    - Test warning includes both fund details
    - Test empty list when no unresolved warnings
    - _Requirements: 12.1, 12.2, 12.3_

- [x] 11. Register API routes
  - [x] 11.1 Add all API routes to routes/api.php
    - Register Fund resource routes
    - Register FundManager resource routes (store, index, destroy only)
    - Register Company resource routes (store, index, destroy only)
    - Register DuplicateWarning index route
    - _Requirements: All API requirements_

- [x] 12. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 13. Implement Vue.js frontend structure
  - [x] 13.1 Set up Vue.js project with Vue Router
    - Initialize Vue 3 project with Vite
    - Install Vue Router and Axios
    - Configure API base URL for Laravel backend/
    - Create main App.vue with router-view
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5, 13.6_
  
  - [x] 13.2 Create router configuration
    - Define routes for fund list, fund create, fund edit
    - Define route for duplicate warnings list
    - _Requirements: 13.1, 13.2, 13.3, 13.6_

- [x] 14. Implement Vue.js components for Fund management
  - [x] 14.1 Create FundList.vue component
    - Display paginated list of funds with name, start year, manager
    - Show aliases and companies for each fund
    - Implement delete button with confirmation
    - Add navigation to edit form
    - _Requirements: 13.1, 13.4_
  
  - [x] 14.2 Create FilterBar.vue component
    - Create input fields for name, fund manager, year, company filters
    - Emit filter change events to parent component
    - Apply filters to fund list API call
    - _Requirements: 13.5_
  
  - [x] 14.3 Create FundForm.vue component
    - Create form with fields for name, start year, fund manager
    - Implement dynamic alias input fields (add/remove)
    - Implement multi-select for company associations
    - Handle both create and edit modes
    - Display validation errors from API
    - Submit to appropriate API endpoint (POST or PUT)
    - _Requirements: 13.2, 13.3_
  
  - [ ] 14.4 Write unit tests for Vue components
    - Test FundList renders funds correctly
    - Test FilterBar emits filter events
    - Test FundForm validation and submission
    - Test delete confirmation flow
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5_

- [x] 15. Implement Vue.js component for duplicate warnings
  - [x] 15.1 Create DuplicateWarningList.vue component
    - Fetch unresolved warnings from API
    - Display side-by-side comparison of duplicate fund pairs
    - Show fund names, aliases, managers, and companies
    - Format timestamps for readability
    - _Requirements: 13.6_
  
  - [ ]* 15.2 Write unit tests for DuplicateWarningList
    - Test warning list renders correctly
    - Test side-by-side fund comparison display
    - Test empty state when no warnings
    - _Requirements: 13.6_

- [x] 16. Implement API service layer in Vue.js
  - [x] 16.1 Create API service module
    - Create axios instance with base URL configuration
    - Implement fund API methods (list, get, create, update, delete)
    - Implement fund manager API methods (list, create, delete)
    - Implement company API methods (list, create, delete)
    - Implement duplicate warning API method (list)
    - Add error handling and response interceptors
    - _Requirements: All API-related UI requirements_

- [x] 17. Integration and final wiring
  - [x] 17.1 Configure CORS in Laravel
    - Enable CORS for Vue.js frontend origin
    - Configure allowed methods and headers
    - _Requirements: All UI requirements_
  
  - [x] 17.2 Set up queue worker configuration
    - Configure Redis queue driver in config/queue.php
    - Document command to run queue worker (php artisan queue:work)
    - _Requirements: 11.1, 11.2, 11.3_
  
  - [x] 17.3 Create seed data for development
    - Create database seeder with sample fund managers
    - Create sample companies
    - Create sample funds with aliases and associations
    - _Requirements: All (development support)_
  
  - [ ]* 17.4 Write integration tests for end-to-end flows
    - Test complete fund creation flow with duplicate detection
    - Test fund update triggering duplicate warning
    - Test filtering funds by various criteria
    - Test soft delete cascading behavior
    - Test event emission and consumer processing
    - _Requirements: 4.1, 4.2, 4.3, 4.5, 5.1, 5.2, 5.3, 5.4, 5.5, 6.1, 6.2, 6.3, 6.5, 10.1, 10.2, 10.3, 10.4, 10.5, 11.1, 11.2, 11.3_

- [x] 18. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- All property-based tests should run minimum 100 iterations using PHPUnit with Faker
- Property tests should be tagged with `@group property-based` and `@group canoe-app-funds`
- The queue worker must be running for duplicate detection to work: `php artisan queue:work`
- Vue.js frontend should be run separately from Laravel API during development
- Each task references specific requirements for traceability
- Database migrations should be run before model implementation: `php artisan migrate`
- Factories should be used extensively in tests for realistic data generation

import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, CheckCircle, XCircle, AlertTriangle, Play, Upload, FileCheck, Eye, EyeOff } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useState, useMemo } from 'react';

interface ValidationResult {
    valid: boolean;
    errors: string[];
    existing?: {
        principles: string[];
        insights: string[];
        tags: string[];
        dimensions: string[];
        practice_mode: string | null;
    };
}

interface TestRunResult {
    valid: boolean;
    errors: string[];
    warnings: string[];
    would_create: {
        principles: string[];
        insights: string[];
        tags: string[];
        dimensions: string[];
        practice_mode: string | null;
        drills: number;
    } | null;
    would_link_existing: {
        principles: string[];
        insights: string[];
        tags: string[];
        dimensions: string[];
    } | null;
}

interface ImportResult {
    success: boolean;
    message?: string;
    errors?: string[];
    created?: TestRunResult['would_create'];
    linked?: TestRunResult['would_link_existing'];
    warnings?: string[];
    practice_mode_id?: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/users' },
    { title: 'Practice Modes', href: '/admin/practice-modes' },
    { title: 'Bulk Import', href: '/admin/practice-modes-import' },
];

export default function PracticeModeImport() {
    const [json, setJson] = useState('');
    const [loading, setLoading] = useState<'validate' | 'test' | 'import' | null>(null);
    const [validationResult, setValidationResult] = useState<ValidationResult | null>(null);
    const [testResult, setTestResult] = useState<TestRunResult | null>(null);
    const [importResult, setImportResult] = useState<ImportResult | null>(null);
    const [showPreview, setShowPreview] = useState(false);

    // Build a set of existing slugs/keys for highlighting
    const existingKeys = useMemo(() => {
        if (!validationResult?.existing) return new Set<string>();
        const ex = validationResult.existing;
        const keys = new Set<string>();
        if (ex.practice_mode) keys.add(ex.practice_mode);
        ex.principles.forEach(s => keys.add(s));
        ex.insights.forEach(s => keys.add(s));
        ex.tags.forEach(s => keys.add(s));
        ex.dimensions.forEach(s => keys.add(s));
        return keys;
    }, [validationResult]);

    // Render JSON with highlighted existing slugs
    const renderHighlightedJson = useMemo(() => {
        if (!json.trim() || existingKeys.size === 0) return null;

        try {
            const formatted = JSON.stringify(JSON.parse(json), null, 2);
            // Split into lines and process each
            const lines = formatted.split('\n');

            return lines.map((line, i) => {
                // Check if this line contains a slug or key that exists
                let highlighted = false;
                let matchedKey = '';

                // Match patterns like "slug": "value" or "key": "value" or "principle": "value"
                const slugMatch = line.match(/"(slug|key|principle|primary_insight)":\s*"([^"]+)"/);
                if (slugMatch && existingKeys.has(slugMatch[2])) {
                    highlighted = true;
                    matchedKey = slugMatch[2];
                }

                // Also check tags array items like "tag-slug"
                const tagMatch = line.match(/^\s*"([^"]+)"(,?)$/);
                if (tagMatch && existingKeys.has(tagMatch[1])) {
                    highlighted = true;
                    matchedKey = tagMatch[1];
                }

                // Check dimensions array items
                const dimMatch = line.match(/^\s*"([^"]+)"(,?)$/);
                if (dimMatch && existingKeys.has(dimMatch[1])) {
                    highlighted = true;
                    matchedKey = dimMatch[1];
                }

                if (highlighted) {
                    // Highlight the matched key within the line
                    const parts = line.split(matchedKey);
                    return (
                        <div key={i} className="bg-yellow-100 dark:bg-yellow-900/40">
                            <span className="text-neutral-500 select-none w-8 inline-block text-right mr-2">{i + 1}</span>
                            {parts[0]}
                            <span className="bg-yellow-300 dark:bg-yellow-700 px-0.5 rounded">{matchedKey}</span>
                            {parts.slice(1).join(matchedKey)}
                        </div>
                    );
                }

                return (
                    <div key={i}>
                        <span className="text-neutral-500 select-none w-8 inline-block text-right mr-2">{i + 1}</span>
                        {line}
                    </div>
                );
            });
        } catch {
            return null;
        }
    }, [json, existingKeys]);

    const handleValidate = async () => {
        setLoading('validate');
        setValidationResult(null);
        setTestResult(null);
        setImportResult(null);
        setShowPreview(false);

        try {
            const response = await fetch('/admin/practice-modes-import/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ json }),
            });
            const result = await response.json();
            setValidationResult(result);

            // Auto-show preview if there are existing items
            const ex = result.existing;
            if (ex && (ex.practice_mode || ex.principles?.length || ex.insights?.length || ex.tags?.length || ex.dimensions?.length)) {
                setShowPreview(true);
            }
        } catch {
            setValidationResult({ valid: false, errors: ['Network error'] });
        } finally {
            setLoading(null);
        }
    };

    const handleTestRun = async () => {
        setLoading('test');
        setTestResult(null);
        setImportResult(null);

        try {
            const response = await fetch('/admin/practice-modes-import/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ json }),
            });
            const result = await response.json();
            setTestResult(result);
            if (result.valid) {
                setValidationResult({ valid: true, errors: [] });
            }
        } catch {
            setTestResult({ valid: false, errors: ['Network error'], warnings: [], would_create: null, would_link_existing: null });
        } finally {
            setLoading(null);
        }
    };

    const handleImport = async () => {
        setLoading('import');
        setImportResult(null);

        try {
            const response = await fetch('/admin/practice-modes-import/execute', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ json }),
            });
            const result = await response.json();
            setImportResult(result);
        } catch {
            setImportResult({ success: false, errors: ['Network error'] });
        } finally {
            setLoading(null);
        }
    };

    const renderList = (items: string[], label: string) => {
        if (!items || items.length === 0) return null;
        return (
            <div className="text-sm">
                <span className="font-medium">{label}:</span>{' '}
                <span className="text-neutral-600 dark:text-neutral-400">{items.join(', ')}</span>
            </div>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Bulk Import Practice Mode" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-4xl">
                <div className="flex items-center gap-4">
                    <Link href="/admin/practice-modes">
                        <Button variant="ghost" size="sm">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back
                        </Button>
                    </Link>
                </div>

                <div>
                    <h1 className="text-2xl font-bold">Bulk Import</h1>
                    <p className="text-neutral-500 mt-1">
                        Paste JSON to import a complete practice mode with principles, insights, tags, dimensions, and drills.
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>JSON Content</CardTitle>
                        <CardDescription>
                            Paste your practice mode JSON below. All created items will default to inactive.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <Textarea
                            value={json}
                            onChange={(e) => setJson(e.target.value)}
                            placeholder='{"practice_mode": {...}, "drills": [...], ...}'
                            rows={20}
                            className="font-mono text-sm"
                        />

                        <div className="flex gap-2 flex-wrap">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleValidate}
                                disabled={!json.trim() || loading !== null}
                            >
                                <FileCheck className="h-4 w-4 mr-2" />
                                {loading === 'validate' ? 'Validating...' : 'Validate Schema'}
                            </Button>

                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleTestRun}
                                disabled={!json.trim() || loading !== null}
                            >
                                <Play className="h-4 w-4 mr-2" />
                                {loading === 'test' ? 'Running...' : 'Test Run'}
                            </Button>

                            <Button
                                type="button"
                                onClick={handleImport}
                                disabled={!json.trim() || loading !== null || !testResult?.valid}
                            >
                                <Upload className="h-4 w-4 mr-2" />
                                {loading === 'import' ? 'Importing...' : 'Import'}
                            </Button>

                            {existingKeys.size > 0 && (
                                <Button
                                    type="button"
                                    variant="ghost"
                                    onClick={() => setShowPreview(!showPreview)}
                                    className="ml-auto"
                                >
                                    {showPreview ? <EyeOff className="h-4 w-4 mr-2" /> : <Eye className="h-4 w-4 mr-2" />}
                                    {showPreview ? 'Hide' : 'Show'} Highlights
                                </Button>
                            )}
                        </div>

                        {showPreview && renderHighlightedJson && (
                            <div className="border rounded-lg bg-neutral-50 dark:bg-neutral-900 p-3 overflow-x-auto max-h-96 overflow-y-auto">
                                <div className="text-xs text-neutral-500 mb-2 flex items-center gap-2">
                                    <span className="bg-yellow-300 dark:bg-yellow-700 px-1.5 py-0.5 rounded text-yellow-900 dark:text-yellow-100">Yellow</span>
                                    = Already exists in database
                                </div>
                                <pre className="text-xs font-mono whitespace-pre">{renderHighlightedJson}</pre>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Validation Result */}
                {validationResult && (
                    <Card className={validationResult.valid ? 'border-green-200 dark:border-green-800' : 'border-red-200 dark:border-red-800'}>
                        <CardHeader className="pb-2">
                            <CardTitle className="flex items-center gap-2 text-lg">
                                {validationResult.valid ? (
                                    <>
                                        <CheckCircle className="h-5 w-5 text-green-600" />
                                        Schema Valid
                                    </>
                                ) : (
                                    <>
                                        <XCircle className="h-5 w-5 text-red-600" />
                                        Schema Invalid
                                    </>
                                )}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {validationResult.errors.length > 0 && (
                                <ul className="text-sm text-red-600 dark:text-red-400 space-y-1">
                                    {validationResult.errors.map((error, i) => (
                                        <li key={i}>- {error}</li>
                                    ))}
                                </ul>
                            )}

                            {validationResult.existing && (
                                (() => {
                                    const ex = validationResult.existing;
                                    const hasLinkable = ex.principles.length > 0 || ex.insights.length > 0 || ex.tags.length > 0 || ex.dimensions.length > 0;

                                    return (
                                        <>
                                            {/* Blocker: Practice mode exists */}
                                            {ex.practice_mode && (
                                                <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded p-3">
                                                    <div className="flex items-center gap-2 text-red-800 dark:text-red-200 font-medium mb-1">
                                                        <XCircle className="h-4 w-4" />
                                                        Blocker
                                                    </div>
                                                    <p className="text-sm text-red-700 dark:text-red-300">
                                                        Practice mode <span className="font-mono font-medium">"{ex.practice_mode}"</span> already exists.
                                                        Change the slug or delete the existing mode first.
                                                    </p>
                                                </div>
                                            )}

                                            {/* Informational: Linkable items */}
                                            {hasLinkable && (
                                                <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded p-3">
                                                    <div className="flex items-center gap-2 text-blue-800 dark:text-blue-200 font-medium mb-1">
                                                        <CheckCircle className="h-4 w-4" />
                                                        Will Link to Existing
                                                    </div>
                                                    <p className="text-xs text-blue-600 dark:text-blue-400 mb-2">
                                                        These already exist and will be reused - not overwritten or duplicated.
                                                    </p>
                                                    <div className="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                                                        {renderList(ex.principles, 'Principles')}
                                                        {renderList(ex.insights, 'Insights')}
                                                        {renderList(ex.tags, 'Tags')}
                                                        {renderList(ex.dimensions, 'Dimensions')}
                                                    </div>
                                                </div>
                                            )}
                                        </>
                                    );
                                })()
                            )}
                        </CardContent>
                    </Card>
                )}

                {/* Test Run Result */}
                {testResult && testResult.valid && (
                    <Card className="border-blue-200 dark:border-blue-800">
                        <CardHeader className="pb-2">
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <CheckCircle className="h-5 w-5 text-blue-600" />
                                Test Run Results
                            </CardTitle>
                            <CardDescription>Preview of what will be created</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {testResult.warnings.length > 0 && (
                                <div className="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded p-3">
                                    <div className="flex items-center gap-2 text-yellow-800 dark:text-yellow-200 font-medium mb-1">
                                        <AlertTriangle className="h-4 w-4" />
                                        Warnings
                                    </div>
                                    <ul className="text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                                        {testResult.warnings.map((warning, i) => (
                                            <li key={i}>- {warning}</li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            {testResult.would_create && (
                                <div className="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded p-3">
                                    <div className="font-medium text-green-800 dark:text-green-200 mb-2">Will Create:</div>
                                    <div className="space-y-1">
                                        {testResult.would_create.practice_mode && (
                                            <div className="text-sm">
                                                <span className="font-medium">Practice Mode:</span>{' '}
                                                <span className="text-neutral-600 dark:text-neutral-400">{testResult.would_create.practice_mode}</span>
                                            </div>
                                        )}
                                        <div className="text-sm">
                                            <span className="font-medium">Drills:</span>{' '}
                                            <span className="text-neutral-600 dark:text-neutral-400">{testResult.would_create.drills}</span>
                                        </div>
                                        {renderList(testResult.would_create.principles, 'Principles')}
                                        {renderList(testResult.would_create.insights, 'Insights')}
                                        {renderList(testResult.would_create.tags, 'Tags')}
                                        {renderList(testResult.would_create.dimensions, 'Dimensions')}
                                    </div>
                                </div>
                            )}

                            {testResult.would_link_existing && (
                                <div className="bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded p-3">
                                    <div className="font-medium text-neutral-700 dark:text-neutral-300 mb-2">Will Link Existing:</div>
                                    <div className="space-y-1">
                                        {renderList(testResult.would_link_existing.principles, 'Principles')}
                                        {renderList(testResult.would_link_existing.insights, 'Insights')}
                                        {renderList(testResult.would_link_existing.tags, 'Tags')}
                                        {renderList(testResult.would_link_existing.dimensions, 'Dimensions')}
                                        {Object.values(testResult.would_link_existing).every(arr => arr.length === 0) && (
                                            <div className="text-sm text-neutral-500">None</div>
                                        )}
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}

                {/* Import Result */}
                {importResult && (
                    <Card className={importResult.success ? 'border-green-500 dark:border-green-600 border-2 bg-green-50 dark:bg-green-900/20' : 'border-red-200 dark:border-red-800'}>
                        <CardHeader className="pb-2">
                            <CardTitle className="flex items-center gap-2 text-xl">
                                {importResult.success ? (
                                    <>
                                        <CheckCircle className="h-6 w-6 text-green-600" />
                                        Import Successful!
                                    </>
                                ) : (
                                    <>
                                        <XCircle className="h-5 w-5 text-red-600" />
                                        Import Failed
                                    </>
                                )}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {importResult.success ? (
                                <div className="space-y-4">
                                    {/* Summary */}
                                    {importResult.created && (
                                        <div className="grid grid-cols-2 gap-4">
                                            <div className="bg-white dark:bg-neutral-800 rounded-lg p-3 border">
                                                <div className="text-sm font-medium text-neutral-500 mb-2">Created</div>
                                                <div className="space-y-1 text-sm">
                                                    {importResult.created.practice_mode && (
                                                        <div><span className="font-medium">Practice Mode:</span> {importResult.created.practice_mode}</div>
                                                    )}
                                                    <div><span className="font-medium">Drills:</span> {importResult.created.drills}</div>
                                                    {importResult.created.principles.length > 0 && (
                                                        <div><span className="font-medium">Principles:</span> {importResult.created.principles.length}</div>
                                                    )}
                                                    {importResult.created.insights.length > 0 && (
                                                        <div><span className="font-medium">Insights:</span> {importResult.created.insights.length}</div>
                                                    )}
                                                    {importResult.created.tags.length > 0 && (
                                                        <div><span className="font-medium">Tags:</span> {importResult.created.tags.length}</div>
                                                    )}
                                                    {importResult.created.dimensions.length > 0 && (
                                                        <div><span className="font-medium">Dimensions:</span> {importResult.created.dimensions.length}</div>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="bg-white dark:bg-neutral-800 rounded-lg p-3 border">
                                                <div className="text-sm font-medium text-neutral-500 mb-2">Linked Existing</div>
                                                <div className="space-y-1 text-sm">
                                                    {importResult.linked?.principles.length ? (
                                                        <div><span className="font-medium">Principles:</span> {importResult.linked.principles.length}</div>
                                                    ) : null}
                                                    {importResult.linked?.insights.length ? (
                                                        <div><span className="font-medium">Insights:</span> {importResult.linked.insights.length}</div>
                                                    ) : null}
                                                    {importResult.linked?.tags.length ? (
                                                        <div><span className="font-medium">Tags:</span> {importResult.linked.tags.length}</div>
                                                    ) : null}
                                                    {importResult.linked?.dimensions.length ? (
                                                        <div><span className="font-medium">Dimensions:</span> {importResult.linked.dimensions.length}</div>
                                                    ) : null}
                                                    {!importResult.linked?.principles.length && !importResult.linked?.insights.length && !importResult.linked?.tags.length && !importResult.linked?.dimensions.length && (
                                                        <div className="text-neutral-400">None</div>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    )}

                                    {/* Warnings */}
                                    {importResult.warnings && importResult.warnings.length > 0 && (
                                        <div className="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded p-3">
                                            <div className="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-1">Warnings</div>
                                            <ul className="text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                                                {importResult.warnings.map((w, i) => <li key={i}>- {w}</li>)}
                                            </ul>
                                        </div>
                                    )}

                                    {/* Action buttons */}
                                    <div className="flex gap-2 pt-2">
                                        {importResult.practice_mode_id && (
                                            <Link href={`/admin/practice-modes/${importResult.practice_mode_id}/edit`}>
                                                <Button>
                                                    Edit Practice Mode
                                                </Button>
                                            </Link>
                                        )}
                                        <Link href="/admin/practice-modes">
                                            <Button variant="outline">
                                                Back to List
                                            </Button>
                                        </Link>
                                    </div>

                                    <p className="text-sm text-neutral-500">
                                        Note: All created items are set to <span className="font-medium">inactive</span> by default. Edit them to activate.
                                    </p>
                                </div>
                            ) : (
                                <ul className="text-sm text-red-600 dark:text-red-400 space-y-1">
                                    {importResult.errors?.map((error, i) => (
                                        <li key={i}>- {error}</li>
                                    ))}
                                </ul>
                            )}
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}

<?php

namespace App\Jobs;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Agents\VerifyPromptOutputDto;
use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Documents\StatusEnum;
use App\Domains\Prompts\SummarizeDocumentPrompt;
use App\Events\CollectionStatusEvent;
use App\Models\Document;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\Helpers\TrimText;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;

class SummarizeDocumentJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected string $results = '';

    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $content = [];

        foreach ($this->document->document_chunks as $chunk) {
            $content[] = (new TrimText())->handle($chunk->content);
        }

        $content = implode(' ', $content);

        Log::info('[LaraChain] Summarizing Document', [
            'token_count_v2' => token_counter_v2($content),
            'token_count_v1' => token_counter($content),
        ]);

        $prompt = SummarizeDocumentPrompt::prompt($content);

        /** @var CompletionResponse $results */
        $results = LlmDriverFacade::driver(
            $this->document->getDriver()
        )->completion($prompt);

        $this->results = $results->content;

        if(Feature::active("verification_prompt_summary")) {

            $verifyPrompt = <<<'PROMPT'
            This the content from all the documents in this collection.
            Then that was passed into the LLM to summarize the results.
            PROMPT;
    
            $dto = VerifyPromptInputDto::from(
                [
                    'chattable' => $this->document->collection,
                    'originalPrompt' => $prompt,
                    'context' => $content,
                    'llmResponse' =>  $this->results,
                    'verifyPrompt' => $verifyPrompt,
                ]
            );
    
            /** @var VerifyPromptOutputDto $response */
            $response = VerifyResponseAgent::verify($dto);

            $this->results = $response->response;

        }

        $this->document->update([
            'summary' => $this->results,
            'status_summary' => StatusEnum::Complete,
        ]);

        notify_collection_ui($this->document->collection, CollectionStatusEnum::PROCESSED, 'Document Summarized');

        DocumentProcessingCompleteJob::dispatch($this->document);
    }
}

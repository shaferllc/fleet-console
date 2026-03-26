<div class="rounded-xl border border-zinc-700/60 bg-zinc-950/40 p-4 sm:p-5">
    <h3 class="fc-heading text-base font-semibold tracking-tight text-white">What to add where</h3>
    <p class="mt-2 text-xs leading-relaxed text-zinc-500">
        Each row is one kind of value and where it belongs. The <strong class="font-medium text-zinc-400">operator bearer secret</strong>
        is <strong class="font-medium text-zinc-400">per service</strong>: set it on the target app and store the same value in Fleet using this form’s <strong class="font-medium text-zinc-400">Operator token</strong> field.
    </p>

    <div class="mt-4 overflow-x-auto rounded-lg border border-zinc-800/80">
        <table class="min-w-[640px] w-full border-collapse text-left text-xs sm:text-sm">
            <thead>
                <tr class="border-b border-zinc-800 bg-zinc-900/80">
                    <th scope="col" class="px-3 py-2.5 font-semibold text-zinc-300">What</th>
                    <th scope="col" class="px-3 py-2.5 font-semibold text-zinc-300">Where you set it</th>
                    <th scope="col" class="px-3 py-2.5 font-semibold text-zinc-300">Example / note</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800/90 text-zinc-400">
                <tr class="align-top">
                    <td class="px-3 py-2.5 font-medium text-zinc-300">Operator bearer secret</td>
                    <td class="px-3 py-2.5">Target app <code class="whitespace-nowrap rounded bg-zinc-950 px-1 py-0.5 font-mono text-[11px] text-cyan-200/90">.env</code></td>
                    <td class="px-3 py-2.5 font-mono text-[11px] text-zinc-500 sm:text-xs">FLEET_OPERATOR_TOKEN=…</td>
                </tr>
                <tr class="align-top">
                    <td class="px-3 py-2.5 font-medium text-zinc-300">Same secret for Fleet to send</td>
                    <td class="px-3 py-2.5">This form: <strong class="text-zinc-300">Operator token</strong></td>
                    <td class="px-3 py-2.5">Stored per service in Fleet (encrypted). Must equal the target app’s operator token.</td>
                </tr>
                <tr class="align-top">
                    <td class="px-3 py-2.5 font-medium text-zinc-300">Console login</td>
                    <td class="px-3 py-2.5">Fleet <code class="whitespace-nowrap rounded bg-zinc-950 px-1 py-0.5 font-mono text-[11px] text-cyan-200/90">.env</code></td>
                    <td class="px-3 py-2.5 font-mono text-[11px] text-zinc-500 sm:text-xs">FLEET_CONSOLE_PASSWORD_HASH=…</td>
                </tr>
                <tr class="align-top">
                    <td class="px-3 py-2.5 font-medium text-zinc-300">TLS errors when polling</td>
                    <td class="px-3 py-2.5">Fleet <code class="whitespace-nowrap rounded bg-zinc-950 px-1 py-0.5 font-mono text-[11px] text-cyan-200/90">.env</code></td>
                    <td class="px-3 py-2.5 font-mono text-[11px] text-zinc-500 sm:text-xs">FLEET_CONSOLE_HTTP_VERIFY=false</td>
                </tr>
                <tr class="align-top">
                    <td class="px-3 py-2.5 font-medium text-zinc-300">Read API token</td>
                    <td class="px-3 py-2.5">Fleet <code class="whitespace-nowrap rounded bg-zinc-950 px-1 py-0.5 font-mono text-[11px] text-cyan-200/90">.env</code></td>
                    <td class="px-3 py-2.5 font-mono text-[11px] text-zinc-500 sm:text-xs">FLEET_CONSOLE_API_TOKEN=…</td>
                </tr>
                <tr class="align-top border-t border-zinc-800/90">
                    <td class="px-3 py-2.5 font-medium text-zinc-300">Service slug</td>
                    <td class="px-3 py-2.5">This form: <strong class="text-zinc-300">Key</strong></td>
                    <td class="px-3 py-2.5 font-mono text-[11px] text-cyan-200/90 sm:text-xs">waypost</td>
                </tr>
                <tr class="align-top">
                    <td class="px-3 py-2.5 font-medium text-zinc-300">Dashboard title</td>
                    <td class="px-3 py-2.5">This form: <strong class="text-zinc-300">Display name</strong></td>
                    <td class="px-3 py-2.5">e.g. Waypost</td>
                </tr>
                <tr class="align-top">
                    <td class="px-3 py-2.5 font-medium text-zinc-300">Card subtitle</td>
                    <td class="px-3 py-2.5">This form: <strong class="text-zinc-300">Description</strong></td>
                    <td class="px-3 py-2.5">Optional</td>
                </tr>
                <tr class="align-top">
                    <td class="px-3 py-2.5 font-medium text-zinc-300">Poll URL origin</td>
                    <td class="px-3 py-2.5">This form: <strong class="text-zinc-300">Operator base URL</strong></td>
                    <td class="px-3 py-2.5 font-mono text-[11px] text-cyan-200/90 sm:text-xs">https://waypost.dply.io — no path</td>
                </tr>
                <tr class="align-top">
                    <td class="px-3 py-2.5 font-medium text-zinc-300">Open site link</td>
                    <td class="px-3 py-2.5">This form: <strong class="text-zinc-300">Public site URL</strong></td>
                    <td class="px-3 py-2.5">Optional; else uses operator base URL</td>
                </tr>
                <tr class="align-top">
                    <td class="px-3 py-2.5 font-medium text-zinc-300">Operator API path</td>
                    <td class="px-3 py-2.5">This form: <strong class="text-zinc-300">Operator path prefix</strong></td>
                    <td class="px-3 py-2.5 font-mono text-[11px] text-cyan-200/90 sm:text-xs">/api/operator</td>
                </tr>
                <tr class="align-top">
                    <td class="px-3 py-2.5 font-medium text-zinc-300">List order</td>
                    <td class="px-3 py-2.5">This form: <strong class="text-zinc-300">Sort order</strong></td>
                    <td class="px-3 py-2.5">Lower first</td>
                </tr>
                <tr class="align-top">
                    <td class="px-3 py-2.5 font-medium text-zinc-300">Include in polls</td>
                    <td class="px-3 py-2.5">This form: <strong class="text-zinc-300">Enabled</strong></td>
                    <td class="px-3 py-2.5">Off hides from dashboard</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

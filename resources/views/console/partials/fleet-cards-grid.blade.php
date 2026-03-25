<ul id="fleet-cards-grid" class="mt-10 grid gap-5 lg:grid-cols-2" tabindex="-1">
    @foreach ($results as $row)
        @include('console.partials.fleet-target-card', ['row' => $row])
    @endforeach
</ul>

<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.ea-yoga')]  class extends Component {}
?>
<x-slot name="title">
    {{ tenant('name') }} - About
</x-slot>

<div>


</div>
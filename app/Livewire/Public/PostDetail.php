<?php

namespace App\Livewire\Public;

use App\Models\Post; // Sesuaikan dengan nama Model berita kamu (misal: Post atau News)
use Livewire\Component;

class PostDetail extends Component
{
    public Post $post;

    // Route Model Binding otomatis mengisi $post berdasarkan slug/id di URL
    public function mount(Post $post)
    {
        $this->post = $post;
    }

    public function render()
    {
        return view('livewire.public.post-detail')
            ->layout('components.layouts.app'); // Sesuaikan dengan layout frontend kamu
    }
}

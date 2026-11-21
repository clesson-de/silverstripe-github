<div class="github-repository-list">
    <% if $ShowTitle && $Title %>
        <h2 class="github-repository-list__title">$Title</h2>
    <% end_if %>

    <% if $FilteredRepositories %>
        <div class="github-repository-list__items">
            <% loop $FilteredRepositories %>
                <div class="github-repository-list__item">
                    <h3 class="github-repository-list__name">
                        <a href="$HtmlUrl" target="_blank" rel="noopener noreferrer">$Name</a>
                    </h3>
                    <% if $Description %>
                        <p class="github-repository-list__description">$Description</p>
                    <% end_if %>
                    <div class="github-repository-list__meta">
                        <% if $Language %>
                            <span class="github-repository-list__language">$Language</span>
                        <% end_if %>
                        <% if $StargazersCount %>
                            <span class="github-repository-list__stars">&#9733; $StargazersCount</span>
                        <% end_if %>
                        <% if $ForksCount %>
                            <span class="github-repository-list__forks">&#9906; $ForksCount</span>
                        <% end_if %>
                    </div>
                </div>
            <% end_loop %>
        </div>
    <% else %>
        <p class="github-repository-list__empty"><%t Clesson\Silverstripe\GitHub\Elements\GitHubRepositoryListElement.NO_REPOSITORIES 'No repositories found.' %></p>
    <% end_if %>
</div>


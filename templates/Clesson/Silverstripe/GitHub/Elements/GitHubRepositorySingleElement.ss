<div class="github-repository-single">
    <% if $ShowTitle && $Title %>
        <h2 class="github-repository-single__title">$Title</h2>
    <% end_if %>

    <% if $FilteredRepository %>
        <% with $FilteredRepository %>
            <div class="github-repository-single__item">
                <h3 class="github-repository-single__name">
                    <a href="$HtmlUrl" target="_blank" rel="noopener noreferrer">$PromotionTitle</a>
                </h3>
                <% if $PromotionImage %>
                    <div class="github-repository-single__image">
                        $PromotionImage.ScaleWidth(600)
                    </div>
                <% end_if %>
                <% if $PromotionDescription %>
                    <div class="github-repository-single__description">$PromotionDescription</div>
                <% end_if %>
                <div class="github-repository-single__meta">
                    <% if $Language %>
                        <span class="github-repository-single__language">$Language</span>
                    <% end_if %>
                    <% if $StargazersCount %>
                        <span class="github-repository-single__stars">&#9733; $StargazersCount</span>
                    <% end_if %>
                    <% if $ForksCount %>
                        <span class="github-repository-single__forks">&#9906; $ForksCount</span>
                    <% end_if %>
                    <% if $LatestTag %>
                        <span class="github-repository-single__tag">$LatestTag</span>
                    <% end_if %>
                </div>
            </div>
        <% end_with %>
    <% else %>
        <p class="github-repository-single__empty"><%t Clesson\Silverstripe\GitHub\Elements\GitHubRepositorySingleElement.NO_REPOSITORY 'No repository selected.' %></p>
    <% end_if %>
</div>


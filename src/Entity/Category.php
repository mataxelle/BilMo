<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_category_detail",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="category:read")
 * )
 * 
 * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "app_category_edit",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="category:read", excludeIf = "expr(not is_granted('ROLE_ADMIN'))")
 * )
 * 
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "app_category_delete",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="category:read", excludeIf = "expr(not is_granted('ROLE_ADMIN'))")
 * )
 *
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:read', 'product:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 55)]
    #[Assert\NotBlank(message: "Un nom de catégorie est obligatoire")]
    #[Assert\Length(min: 1, max: 55, minMessage: "Le nom doit faire au moins {{ limit }} caractères", maxMessage: "Le nom ne peut pas faire plus de {{ limit }} caractères")]
    #[Groups(['category:read', 'product:read'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Product::class, orphanRemoval: true)]
    #[Groups(['category:read'])]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }
}
